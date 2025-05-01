import { Middleware } from '@reduxjs/toolkit';
import { apiCall } from '~/api';

/**
 * Middleware that intercepts Redux actions and performs additional operations.
 * Currently, it resets the API state when a logout action is detected.
 * 
 */
const storageMiddleware: Middleware = (store) => (next) => (action: any) => {
  const result = next(action);

  if (action.type === 'auth/logout') {
    store.dispatch(apiCall.util.resetApiState());
  }

  return result;
};

export default storageMiddleware;