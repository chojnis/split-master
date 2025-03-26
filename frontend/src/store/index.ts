import { configureStore } from '@reduxjs/toolkit';
import authReducer from './reducers/authReducer';
import { apiCall } from '~/api';
import storageMiddleware from '~/store/middleware/storageMiddleware';

const store = configureStore({
  reducer: {
    auth: authReducer,
    [apiCall.reducerPath]: apiCall.reducer,
  },
  middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(apiCall.middleware, storageMiddleware),
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
export default store;
