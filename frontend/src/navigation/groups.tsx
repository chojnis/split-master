import { createNativeStackNavigator } from '@react-navigation/native-stack';
import GroupsScreen from '~/screens/groups/groups';
import GroupDetailsScreen from '~/screens/groups/groupDetails';
import UserDetailsScreen from '~/screens/user/userDetails';
import AddGroupScreen from '~/screens/groups/addGroup';
import AddTransactionScreen from '~/screens/groups/addTransaction';
import ToggleTheme from '~/components/ToogleTheme';

export type GroupsStackParamList = {
    GroupsList: undefined;
    GroupDetails: { groupId: string };
    TransactionDetails: undefined;
    AddTransaction: { groupId: string };
    AddGroup: undefined;
    UserDetails: { userId: string};
};

const GroupsStackNavigator = createNativeStackNavigator<GroupsStackParamList>();

const GroupsStack = () => {
    return (
        <GroupsStackNavigator.Navigator
            screenOptions = {{
                headerRight: () => <ToggleTheme />
            }}
        >
            <GroupsStackNavigator.Screen 
                name="GroupsList" 
                options={{
                    title: 'Moje grupy'
                }}
                component={GroupsScreen} 
            />
            <GroupsStackNavigator.Screen 
                name="GroupDetails" 
                options={{
                    title: 'Szczegóły grupy'
                }}
                component={GroupDetailsScreen} 
            />
            <GroupsStackNavigator.Screen 
                name="AddGroup"
                options={{
                    title: 'Dodaj grupę'
                }}
                component={AddGroupScreen} 
            />
            <GroupsStackNavigator.Screen 
                name="AddTransaction"
                options={{
                    title: 'Dodaj transakcję'
                }}
                component={AddTransactionScreen} 
            />
            <GroupsStackNavigator.Screen
                name="UserDetails"
                options={{
                    title: 'Szczegóły użytkownika'
                }}
                component={UserDetailsScreen}
            />
        </GroupsStackNavigator.Navigator>
    );
}

export default GroupsStack;