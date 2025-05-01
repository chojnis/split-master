import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import ProfileScreen from '~/screens/settings';
import SettingsIcon from '~/lib/icons/Settings';
import HouseIcon from '~/lib/icons/House';
import GroupsStack from './groups';

export type RootTabParamList = {
    Groups: undefined;
    Settings: undefined;
};

const RootTabNavigator = createBottomTabNavigator<RootTabParamList>();

/**
 * Root Tab Navigation component.
 * 
 * Defines the main tab navigation structure of the application with two tabs:
 * 1. Groups - Displays the GroupsStack component with a house icon
 * 2. Settings - Displays the ProfileScreen component with a settings icon
 * 
 */
const RootTab = () => {
    return (
      <RootTabNavigator.Navigator>
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
  