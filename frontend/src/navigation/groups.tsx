import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { StackNavigationProp } from '@react-navigation/stack';
import GroupsScreen from '~/screens/groups/groups';
import GroupDetailsScreen from '~/screens/groups/groupDetails';
import GroupSettingsScreen from '~/screens/groups/groupSettings';
import UserDetailsScreen from '~/screens/user/userDetails';
import AddGroupScreen from '~/screens/groups/addGroup';
import AddTransactionScreen from '~/screens/groups/addTransaction';
import EditTransactionScreen from '~/screens/groups/editTransaction';

export type GroupsStackParamList = {
    GroupsList: undefined;
    GroupDetails: { groupId: string };
    TransactionDetails: undefined;
    AddTransaction: { groupId: string, defaultCurrencyId: string };
    EditTransaction: { groupId: string, transactionId: string, defaultCurrencyId: string };
    AddGroup: undefined;
    UserDetails: { userId: string};
    GroupSettings: { groupId: string };
};

const GroupsStackNavigator = createNativeStackNavigator<GroupsStackParamList>();

const GroupsStack = () => {
    return (
        <GroupsStackNavigator.Navigator
            // screenOptions = {{
            //     headerRight: () => <ToggleTheme />
            // }}
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
                name="GroupSettings" 
                options={{
                    title: 'Ustawienia grupy'
                }}
                component={GroupSettingsScreen} 
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
                name="EditTransaction"
                options={{
                    title: 'Edytuj transakcję'
                }}
                component={EditTransactionScreen} 
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