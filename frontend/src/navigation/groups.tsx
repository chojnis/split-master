import { createNativeStackNavigator } from '@react-navigation/native-stack';
import GroupsScreen from '~/screens/groups';
import GroupDetailsScreen from '~/screens/groups/groupDetails';
import GroupSettingsScreen from '~/screens/groups/groupSettings';
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
    GroupSettings: { groupId: string };
};

const GroupsStackNavigator = createNativeStackNavigator<GroupsStackParamList>();

/**
 * The GroupsStack component defines the navigation stack for the Groups section of the application.
 * It manages navigation between various screens related to groups and transactions:
 * - GroupsList: Displays all user groups
 * - GroupDetails: Shows detailed information about a specific group
 * - GroupSettings: Provides options to modify group settings
 * - AddGroup: Interface for creating a new group
 * - TransactionDetails: Displays detailed information about a specific transaction
 * - AddTransaction: Modal for creating a new transaction
 * - EditTransaction: Modal for modifying an existing transaction
 * 
 */
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
        </GroupsStackNavigator.Navigator>
    );
}

export default GroupsStack;