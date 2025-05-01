import { fetchBaseQuery, BaseQueryFn, FetchBaseQueryError, FetchArgs } from '@reduxjs/toolkit/query/react';
import { RootState, AppDispatch } from '~/store';
import { LoginResponse } from '~/api/types/response';
import { RefreshTokenRequest } from '~/api/types/request';
import { ErrorBaseQueryFn, ApiError, Violation } from '~/api/types';

const API_URL = process.env.EXPO_PUBLIC_API_URL;

/**
 * Base query configuration for API requests using fetchBaseQuery.
 * 
 */
const baseQuery = fetchBaseQuery({
    baseUrl: API_URL,
    prepareHeaders: (headers, { getState }) => {
      const token = (getState() as RootState).auth.token;
      if (token) {
        headers.set('authorization', `Bearer ${token}`);
      }
      return headers;
    },
    timeout: 10000
});

/**
 * 
 * Wraps the base query function to handle HTTP 204 No Content responses.
 * When a 204 status is detected, it transforms the response to return an empty object as data.
 * This is useful for APIs that return 204 status without a body when operations succeed but there's no data to return.
 * 
 */
const baseQueryWith204Handler: typeof baseQuery = async (args, api, extraOptions) => {
  const result = await baseQuery(args, api, extraOptions);

  console.log('Base query result:', result);

  if(result.meta?.response?.status === 204) {
    return { data: {} };
  }
  
  return result;
}

/**
 * 
 * A wrapper for baseQueryWith204Handler that handles error responses from the API.
 * 
 */
const baseQueryWithErrorHandling: ErrorBaseQueryFn = async (args, api, extraOptions) => {
  const result = await baseQueryWith204Handler(args, api, extraOptions);

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

/**
 * 
 * This function wraps the standard base query for automatic token refresh capability.
 * 1. Makes the initial API request
 * 2. If a 401 Unauthorized error occurs (except for login endpoints):
 *    - Attempts to refresh the authentication token
 *    - Updates authentication state with new tokens if successful
 *    - Retries the original request with the new token
 *    - Logs out the user if token refresh fails
 * 
 */
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