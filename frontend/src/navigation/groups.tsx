import { createNativeStackNavigator } from '@react-navigation/native-stack';
import GroupsScreen from '~/screens/groups';
import GroupDetailsScreen from '~/screens/groups/groupDetails';
import GroupSettingsScreen from '~/screens/groups/groupSettings';
// import UserDetailsScreen from '~/screens/user/userDetails';
import AddGroupScreen from '~/screens/groups/addGroup';
import AddTransactionScreen from '~/screens/groups/addTransaction';
import EditTransactionScreen from '~/screens/groups/editTransaction';
import TransactionDetailsScreen from '~/screens/groups/transactionDetails';
import { Currency } from '~/api/types/entity';

export type GroupsStackParamList = {
    GroupsList: undefined;
    GroupDetails: { groupId: string };
    AddTransaction: { groupId: string, defaultCurrency: Currency };
    EditTransaction: { groupId: string, transactionId: string, defaultCurrency: Currency };
    TransactionDetails: { groupId: string, transactionId: string, defaultCurrency: Currency };
    AddGroup: undefined;
    // UserDetails: { userId: string};
    GroupSettings: { groupId: string };
};

const GroupsStackNavigator = createNativeStackNavigator<GroupsStackParamList>();

const GroupsStack = () => {
    return (
        <GroupsStackNavigator.Navigator>
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
                name="TransactionDetails"
                options={{
                    title: 'Szczegóły transakcji'
                }}
                component={TransactionDetailsScreen} 
            />
            <GroupsStackNavigator.Screen 
                name="AddTransaction"
                options={{
                    title: 'Dodaj transakcję',
                    presentation: 'modal'
                }}
                component={AddTransactionScreen} 
            />
            <GroupsStackNavigator.Screen 
                name="EditTransaction"
                options={{
                    title: 'Edytuj transakcję',
                    presentation: 'modal'
                }}
                component={EditTransactionScreen} 
            />
            {/* <GroupsStackNavigator.Screen
                name="UserDetails"
                options={{
                    title: 'Szczegóły użytkownika'
                }}
                component={UserDetailsScreen}
            /> */}
        </GroupsStackNavigator.Navigator>
    );
}

export default GroupsStack;