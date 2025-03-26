import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { LoginResponse } from '~/api/response';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { User } from '~/api/entity';
import store from '~/store';

type AuthState = {
  isAuthenticated: boolean;
  token?: string;
  user: User;
};

const initialState: AuthState = {
  isAuthenticated: false,
  token: undefined,
  user: {} as User,
};

export const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    login: (state, action: PayloadAction<LoginResponse>) => {
      state.isAuthenticated = true;
      state.token = action.payload.token;
      state.user = action.payload.user;
      AsyncStorage.setItem('token', action.payload.token);
      AsyncStorage.setItem('user', JSON.stringify(action.payload.user));
    },
    logout: (state) => {
      state.isAuthenticated = false;
      state.user = {} as User;
      AsyncStorage.removeItem('token');
      AsyncStorage.removeItem('user');
    },
  },
});

const loadState = async () => {
  try {
    const token = (await AsyncStorage.getItem('token')) ?? undefined;
    const userString = await AsyncStorage.getItem('user');
    const user = userString ? JSON.parse(userString) as User : {} as User;
    return { isAuthenticated: !!token, token, user };
  } catch (error) {
    console.error('Error loading state from AsyncStorage:', error);
    return initialState;
  }
};

loadState().then((loadedState) => {
  if (loadedState.token !== undefined) {
    store.dispatch(authSlice.actions.login({
      token: loadedState.token,
      user: loadedState.user,
    }));
    // log
    console.log('Loaded state:', loadedState);
  }
  else {
    console.error('Invalid loaded state: Missing token');
  }
});

export const { login, logout } = authSlice.actions;
export default authSlice.reducer;