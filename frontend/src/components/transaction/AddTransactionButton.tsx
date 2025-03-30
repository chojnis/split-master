import { CommonActions, useNavigation, useRoute } from '@react-navigation/native';
import { View, Pressable } from 'react-native';
import { Text } from '~/components/ui/text';
import { GroupsStackParamList } from '~/navigation/groups';
import { RootTabParamList } from '~/navigation/root';
import { StackNavigationProp } from '@react-navigation/stack';
import { BottomTabNavigationProp } from '@react-navigation/bottom-tabs';
import { CompositeNavigationProp } from '@react-navigation/native';
import Plus from '~/lib/icons/Plus';
import { Button } from '../ui/button';

type AddTransactionButtonNavigationProps = CompositeNavigationProp<
  BottomTabNavigationProp<RootTabParamList>,
  StackNavigationProp<GroupsStackParamList>
>;

type RouteParams = {
  groupId?: string;
};

const AddTransactionButton = ({groupId}: {groupId?: string}) => {
//   const navigation = useNavigation<AddTransactionButtonNavigationProps>();
//   const route = useRoute<RouteParams>();

  const handlePress = () => {
    // const groupId = route.params?.groupId;
    // navigation.navigate('Groups', { screen: 'AddTransaction', params: { groupId } });
    console.log('AddTransactionButton groupId', groupId);
  };

  return (
    <Button onPress={handlePress} className="bg-green-500 rounded-md p-3">
      <Plus className="text-white" />
    </Button>
  );
};

export default AddTransactionButton;
