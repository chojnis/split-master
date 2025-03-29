import { useNavigation, useRoute } from '@react-navigation/native';
import { View, Pressable } from 'react-native';
import { Text } from '~/components/ui/text';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';
import { RouteProp } from '@react-navigation/native';
import Plus from '~/lib/icons/Plus';
import { Button } from '../ui/button';

type AddTransactionButtonNavigationProps = StackNavigationProp<GroupsStackParamList>;

const AddTransactionButton = () => {
    const navigation = useNavigation<AddTransactionButtonNavigationProps>();
//   const route = useRoute<RouteProp<GroupsStackParamList>>();
    const state = navigation.getState();
    const route = state.routes[state.index];
    console.log('Current route:', route);
    
    const handlePress = () => {
        const groupId = (route.params as any)?.groupId || undefined;
        console.log('Group ID:', groupId);

        navigation.navigate('AddTransaction', { groupId });
    };

    return (
        <Button onPress={handlePress} className="bg-green-500 rounded-md p-3">
            <Plus className="text-white" />
        </Button>
    );
};

export default AddTransactionButton;