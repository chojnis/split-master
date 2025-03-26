import { Middleware } from '@reduxjs/toolkit';
import AsyncStorage from '@react-native-async-storage/async-storage';


const storageMiddleware: Middleware = (store) => (next) => (action: any) => {
    const result = next(action);

    console.log('storageMiddleware', action.type);

    if (action.type === 'auth/login') {
        console.log('login action', action.payload);
      const { token, refresh_token, user } = action.payload;
      
      const storageOps: Array<[string, string]> = [
        ['token', token],
        ['refreshToken', refresh_token],
        ['user', JSON.stringify(user)]
      ].filter(([_, value]) => value !== undefined) as Array<[string, string]>;
  
      if (storageOps.length > 0) {
        AsyncStorage.multiSet(storageOps).catch(console.error);
      }
    }
  
    if (action.type === 'auth/logout') {
      AsyncStorage.multiRemove(['token', 'refreshToken', 'user'])
        .catch(console.error);
    }
  
    return result;
};

export default storageMiddleware;