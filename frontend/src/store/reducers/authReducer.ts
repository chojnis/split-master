import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { LoginResponse } from '~/api/response';

type AuthState = {
  isAuthenticated: boolean;
  token?: string;
  user: {
    id?: string;
    email?: string;
    username?: string;
  };
}

const initialState: AuthState = {
  isAuthenticated: false,
  token: undefined,
  user: {},
};

export const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    login: (state, action: PayloadAction<LoginResponse>) => {
      state.isAuthenticated = true;
      state.token = action.payload.token;
      state.user = action.payload.user;
    },
    logout: (state) => {
      state.isAuthenticated = false;
      state.user = {};
    },
  },
});

export const { login, logout } = authSlice.actions;
export default authSlice.reducer;
