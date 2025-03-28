import { fetchBaseQuery, BaseQueryFn, FetchBaseQueryError, FetchArgs } from '@reduxjs/toolkit/query/react';
import { RootState, AppDispatch } from '~/store';
import { LoginResponse } from '~/api/types/response';
import { RefreshTokenRequest } from '~/api/types/request';
import { ErrorBaseQueryFn, ApiError, Violation } from '~/api/types';

const BASE_URL = 'https://afc5-217-97-63-46.ngrok-free.app/api/';

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

const baseQueryWithErrorHandling: ErrorBaseQueryFn = async (args, api, extraOptions) => {
  const result = await baseQuery(args, api, extraOptions);

  if (result.data) {
    return { data: result.data };
  }

  const error = result.error as FetchBaseQueryError;

  let standardizedError: ApiError = {
    status: error.status || 500,
    detail: 'Wystąpił błąd. Spróbuj ponownie.'
  };

  if (isObject(error.data)) {
    const apiError = error.data as {
        detail?: string;
        violations?: Violation[];
    };

    if (apiError.detail) {
      standardizedError.detail = apiError.detail;
    }

    if (apiError.violations) {
      standardizedError.violations = apiError.violations;
    }
  }

  return { error: standardizedError };
};

function isObject(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null;
}

const baseQueryWithReauth: ErrorBaseQueryFn = async (args, api, extraOptions) => {
  const { getState, dispatch } = api as {
    getState: () => RootState;
    dispatch: AppDispatch;
  };

  const endpoint = typeof args === 'string' ? args : args.url;

  let result = await baseQueryWithErrorHandling(args, api, extraOptions);
  
  // If 401 error, try to refresh token
  if (result.error?.status === 401 && endpoint !== 'login') {
    let refreshToken = getState().auth.refreshToken;
    
    if (refreshToken) {
      const refreshResult = await baseQueryWithErrorHandling({
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
        result = await baseQueryWithErrorHandling(args, api, extraOptions);
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

export default baseQueryWithReauth;