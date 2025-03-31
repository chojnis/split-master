import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import ProfileScreen from '~/screens/settings';
import AddTransactionScreen from '~/screens/groups/addTransaction';
import ToggleTheme from '~/components/ToogleTheme';
import SettingsIcon from '~/lib/icons/Settings';
import SquarePlusIcon from '~/lib/icons/SquarePlus';
import HouseIcon from '~/lib/icons/House';
import GroupsStack from './groups';
import AddTransactionButton from '~/components/transaction/AddTransactionButton';
import { GroupsStackParamList } from '~/navigation/groups';

export type RootTabParamList = {
    Groups: undefined;
    Settings: undefined;
};

const RootTabNavigator = createBottomTabNavigator<RootTabParamList>();

const RootTab = () => {
    return (
      <RootTabNavigator.Navigator
        // screenOptions = {{
        //   headerRight: () => <ToggleTheme />
        // }}
      >
        <RootTabNavigator.Screen 
          name="Groups"
          options={{
            headerShown: false,
            title: 'Grupy',
            tabBarIcon: ({color, size}) => <HouseIcon color={color} size={size} /> 
          }}
          component={GroupsStack} 
        />
        <RootTabNavigator.Screen 
          name="Settings" 
          options = {{
            title: 'Ustawienia',
            tabBarIcon: ({color, size}) => <SettingsIcon color={color} size={size} />
          }}
          component={ProfileScreen} 
        />
      </RootTabNavigator.Navigator>
    );
}

export default RootTab;
  