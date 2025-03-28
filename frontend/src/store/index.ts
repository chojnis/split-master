import { configureStore, EnhancedStore } from '@reduxjs/toolkit';
import { apiCall } from '~/api';
import storageMiddleware from '~/store/middleware/storageMiddleware';
import authReducer, { loadAuthState, authSlice } from '~/store/reducers/authReducer';

const tempStore = configureStore({
  reducer: {
    auth: authReducer,
    [apiCall.reducerPath]: apiCall.reducer,
  }
});

export const setupStore = async (): Promise<AppStore> => {
  const loadedState = await loadAuthState();
  
  if (loadedState.token && loadedState.refreshToken) {
    tempStore.dispatch(authSlice.actions.login({
      token: loadedState.token,
      refresh_token: loadedState.refreshToken,
      user: loadedState.user,
    }));
  }

  const store = configureStore({
    reducer: {
      auth: authReducer,
      [apiCall.reducerPath]: apiCall.reducer,
    },
    middleware: (getDefaultMiddleware) => 
      getDefaultMiddleware()
        .concat(apiCall.middleware, storageMiddleware),
    preloadedState: {
      auth: loadedState
    }
  });

  return store;
};

// const store = configureStore({
//   reducer: {
//     auth: authReducer,
//     [apiCall.reducerPath]: apiCall.reducer,
//   },
//   middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(apiCall.middleware, storageMiddleware),
// });

export type RootState = ReturnType<typeof tempStore.getState>;
export type AppDispatch = typeof tempStore.dispatch;
export type AppStore = EnhancedStore<RootState>;
// export default store;
