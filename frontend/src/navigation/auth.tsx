import { createNativeStackNavigator } from '@react-navigation/native-stack';
import LoginScreen from '~/screens/auth/login';
import RegisterScreen from '~/screens/auth/register';
import WelcomeScreen from '~/screens/auth';
import ToggleTheme from '~/components/ToogleTheme';

export type AuthStackParamList = {
    Welcome: undefined;
    Login: undefined;
    Register: undefined;
};

const AuthStackNavigator = createNativeStackNavigator<AuthStackParamList>();

const AuthStack = () => {
    return (
      <AuthStackNavigator.Navigator>
        <AuthStackNavigator.Screen 
          name="Welcome" 
          options={{
            title: '',
            headerRight: () => <ToggleTheme />
          }}
          component={WelcomeScreen} 
        />
        <AuthStackNavigator.Group 
          screenOptions={{
            presentation: 'modal'
          }}
        >
          <AuthStackNavigator.Screen 
            name="Login" 
            options={{
              title: 'Zaloguj się'
            }} 
            component={LoginScreen} 
          />
          <AuthStackNavigator.Screen 
            name="Register" 
            options={{
              title: 'Zarejestruj się'
            }} 
            component={RegisterScreen} 
          />
        </AuthStackNavigator.Group>
      </AuthStackNavigator.Navigator>
    );
}

export default AuthStack;