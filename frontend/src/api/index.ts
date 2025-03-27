import { createApi, fetchBaseQuery, BaseQueryFn, FetchArgs, FetchBaseQueryError } from '@reduxjs/toolkit/query/react';
import { RootState, AppDispatch } from '~/store';
import { 
  LoginResponse, 
  RegisterResponse, 
  GroupsResponse, 
  GroupMembersResponse,
  GroupTransactionResponse
} from '~/api/response';
import { LoginRequest, RegisterRequest, RefreshTokenRequest } from '~/api/request';

const BASE_URL = 'https://4dc5-217-97-63-46.ngrok-free.app/api/';

const baseQuery = fetchBaseQuery({
  baseUrl: BASE_URL,
  prepareHeaders: (headers, { getState }) => {
    headers.set('Accept', 'application/json');
    const token = (getState() as RootState).auth.token;
    if (token) {
      headers.set('authorization', `Bearer ${token}`);
    }
    return headers;
  }
});

type CustomBaseQueryFn = BaseQueryFn<
  string | FetchArgs,
  unknown,
  unknown
>;

const baseQueryWithReauth: CustomBaseQueryFn = async (args, api, extraOptions) => {
  const { getState, dispatch } = api as {
    getState: () => RootState;
    dispatch: AppDispatch;
  };

  let result = await baseQuery(args, api, extraOptions);
  
  // If 401 error, try to refresh token
  if (result.error?.status === 401) {
    let refreshToken = getState().auth.refreshToken;
    
    if (refreshToken) {
      const refreshResult = await baseQuery({
        url: 'login/refresh',
        method: 'POST',
        body: { refresh_token: refreshToken } satisfies RefreshTokenRequest,
      }, api, extraOptions);
      
      if (refreshResult.data) {
        // Update auth state with new tokens
        dispatch({
          type: 'auth/login',
          payload: refreshResult.data as LoginResponse,
        });
        
        // Retry the original request with new token
        result = await baseQuery(args, api, extraOptions);
      } else {
        // Refresh failed - logout
        dispatch({ type: 'auth/logout' });
      }
    } else {
      // No refresh token - logout
      dispatch({ type: 'auth/logout' });
    }
  }
  
  return result;
};

export const apiCall = createApi({
  reducerPath: 'api',
  baseQuery: baseQueryWithReauth,
  endpoints: (builder) => ({
    login: builder.mutation<LoginResponse, LoginRequest>({
      query: (credentials) => ({
        url: 'login',
        method: 'POST',
        body: credentials,
      }),
    }),
    register: builder.mutation<RegisterResponse, RegisterRequest>({
      query: (credentials) => ({
        url: 'register',
        method: 'POST',
        body: credentials,
      }),
    }),
    getGroups: builder.query<GroupsResponse, void>({
      query: () => 'groups',
    }),
    getGroupMembers: builder.query<GroupMembersResponse, string>({
      query: (groupId) => ({
        url: `groups/${groupId}/members`,
        method: 'GET',
      }),
    }),
    getGroupTransactions: builder.query<GroupTransactionResponse, string>({
      query: (groupId) => ({
        url: `groups/${groupId}/transactions`,
        method: 'GET',
      }),
    }),
  }),
});

export const { 
  useLoginMutation, 
  useRegisterMutation,
  useGetGroupsQuery, 
  useGetGroupMembersQuery
} = apiCall;