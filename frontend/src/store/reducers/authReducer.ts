import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { LoginResponse } from '~/api/types/response';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { User } from '~/api/types/entity';
// import store from '~/store';
import { apiCall } from '~/api';

export type AuthState = {
  isAuthenticated: boolean;
  token?: string;
  refreshToken?: string;
  user: User;
};

const initialState: AuthState = {
  isAuthenticated: false,
  token: undefined,
  refreshToken: undefined,
  user: {} as User,
};

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
  },
  extraReducers: (builder) => {
    builder.addCase(apiCall.util.resetApiState, (state, action) => {});
  },
});

export const loadAuthState = async (): Promise<AuthState> => {
  try {
    const [token, refreshToken, userString] = await Promise.all([
      AsyncStorage.getItem('token'),
      AsyncStorage.getItem('refreshToken'),
      AsyncStorage.getItem('user')
    ]);

    return {
      isAuthenticated: !!(token && refreshToken),
      token: token || undefined,
      refreshToken: refreshToken || undefined,
      user: userString ? JSON.parse(userString) as User : {} as User
    };
  } catch (error) {
    console.error('Error loading state:', error);
    return initialState;
  }
};
// loadState().then((loadedState) => {
//   if (loadedState.token !== undefined && loadedState.refreshToken !== undefined) {
//     store.dispatch(authSlice.actions.login({
//       token: loadedState.token,
//       refresh_token: loadedState.refreshToken,
//       user: loadedState.user,
//     }));
//     // console.log('Loaded state:', loadedState);
//   }
//   else {
//     // console.error('Invalid loaded state: Missing token');
//   }
// });

export const { login, logout } = authSlice.actions;
export default authSlice.reducer;

export type AuthActions = ReturnType<
  typeof authSlice.actions.login | 
  typeof authSlice.actions.logout
>;