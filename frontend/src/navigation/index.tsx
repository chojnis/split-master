import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';

import LoginScreen from '~/screens/auth/login';
import RegisterScreen from '~/screens/auth/register';
import WelcomeScreen from '~/screens/auth';
import GroupsScreen from '~/screens/groups/groups';
import AddGroupScreen from '~/screens/groups/addGroup';
import ProfileScreen from '~/screens/profile';
import AddTransactionScreen from '~/screens/groups/addTransaction';
import { Button } from 'react-native';

import { useSelector } from 'react-redux';
import { RootState } from '~/store';
import { StackNavigationProp } from '@react-navigation/stack';

export type GroupsStackParamList = {
  GroupsList: undefined;
  GroupDetails: undefined;
  TransactionDetails: undefined;
  AddTransaction: undefined;
  AddGroup: undefined;
};

export type AuthStackParamList = {
  Welcome: undefined;
  Login: undefined;
  Register: undefined;
};

export type RootTabParamList = {
  Groups: undefined;
  AddTransaction: undefined;
  Profile: undefined;
};

const RootTabNavigator = createBottomTabNavigator<RootTabParamList>();
const GroupsStackNavigator = createNativeStackNavigator<GroupsStackParamList>();
const AuthStackNavigator = createNativeStackNavigator<AuthStackParamList>();

function GroupsStack() {
  return (
    <GroupsStackNavigator.Navigator>
      <GroupsStackNavigator.Screen 
        options={({navigation}) => ({
          title: 'Moje grupy', 
          // headerRight: () => (
          //   <Button 
          //     title="Dodaj" 
          //     onPress={() => navigation.navigate("AddGroup")} 
          //   />
          // ),
        })} 
        name="GroupsList" 
        component={GroupsScreen} 
      />
      <GroupsStackNavigator.Screen name="AddGroup" component={AddGroupScreen} />
    </GroupsStackNavigator.Navigator>
  );
}

function RootTab() {
  return (
    <RootTabNavigator.Navigator>
      <RootTabNavigator.Screen options={{headerShown: false}} name="Groups" component={GroupsStack} />
      <RootTabNavigator.Screen name="AddTransaction" component={AddTransactionScreen} />
      <RootTabNavigator.Screen name="Profile" component={ProfileScreen} />
    </RootTabNavigator.Navigator>
  );
}

function AuthStack(){
  return (
    <AuthStackNavigator.Navigator>
      <AuthStackNavigator.Screen name="Welcome" component={WelcomeScreen} />
      <AuthStackNavigator.Group screenOptions={{presentation: 'modal'}}>
        <AuthStackNavigator.Screen options={{title: 'Zaloguj się'}} name="Login" component={LoginScreen} />
        <AuthStackNavigator.Screen options={{title: 'Zarejestruj się'}} name="Register" component={RegisterScreen} />
      </AuthStackNavigator.Group>
    </AuthStackNavigator.Navigator>
  );
}

export default function Navigation() {

  const isAuthenticated = useSelector((state: RootState) => state.auth.isAuthenticated);

  return (
    <NavigationContainer>
      {!isAuthenticated ? <AuthStack /> : <RootTab />}
    </NavigationContainer>
  );
}
