import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { LoginResponse } from '~/api/types/response';
import { User } from '~/api/types/entity';

export type AuthState = {
  isAuthenticated: boolean;
  token?: string;
  refreshToken?: string;
  user?: User;
};

const initialState: AuthState = {
  isAuthenticated: false,
  token: undefined,
  refreshToken: undefined,
  user: undefined,
};

/**
 * Redux slice for authentication state management.
 * 
 * This slice handles user authentication state, including:
 * - Login functionality that stores user token, refresh token and user data
 * - Logout functionality that clears authentication state
 * 
 */
export const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    login: (state, action: PayloadAction<LoginResponse>) => {
      state.isAuthenticated = true;
      state.token = action.payload.token;
      state.refreshToken = action.payload.refresh_token;
      state.user = action.payload.user;
    },
    logout: (state, action: PayloadAction<void>) => {
      state.isAuthenticated = false;
      state.token = undefined;
      state.refreshToken = undefined;
      state.user = {} as User;
    },
  }
});

export const { login, logout } = authSlice.actions;
export default authSlice.reducer;