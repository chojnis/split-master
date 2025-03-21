import { configureStore } from '@reduxjs/toolkit';
import authReducer from './reducers/authReducer';
import { apiCall } from '~/api';
// import exampleReducer from './reducers/exampleReducer';

const store = configureStore({
  reducer: {
    auth: authReducer,
    [apiCall.reducerPath]: apiCall.reducer,
  },
  middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(apiCall.middleware),
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
export default store;
