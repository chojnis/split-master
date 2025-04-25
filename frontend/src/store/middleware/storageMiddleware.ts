import { Middleware } from '@reduxjs/toolkit';
import { apiCall } from '~/api';

const storageMiddleware: Middleware = (store) => (next) => (action: any) => {
  const result = next(action);

  if (action.type === 'auth/logout') {
    store.dispatch(apiCall.util.resetApiState());
  }

  return result;
};

export default storageMiddleware;