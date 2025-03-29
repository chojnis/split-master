import { StyleSheet, View, FlatList, RefreshControl, Pressable } from 'react-native';
import { useGetGroupQuery } from '~/api';
import { Group, User } from '~/api/types/entity';
import { useState } from 'react';
import { useLayoutEffect } from 'react';
import { Button } from '~/components/ui/button';	
import { Text } from '~/components/ui/text';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';
import { Container } from '~/components/Container';
import TransactionsSection from '~/components/group/TransactionSection';
import Loading from '~/components/Loading';
import ErrorText from '~/components/ErrorText';


type GroupDetailsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupDetails'>;
type GroupDetailsScreenRouteProp = RouteProp<GroupsStackParamList, 'GroupDetails'>;

export default function GroupDetails() {
    const router = useRoute<GroupDetailsScreenRouteProp>();
    const groupId = router.params.groupId;

    const { data, isLoading, error, refetch } = useGetGroupQuery(groupId);
    const [refreshing, setRefreshing] = useState(false);
    const navigation = useNavigation<GroupDetailsStackNavigationProp>();

    const onRefresh = async () => {
        setRefreshing(true);
        await refetch();
        setRefreshing(false);
    };

    if (isLoading) return <Loading reverseColors />;
    if (error || !data) {
      return (
        <Container>
          <ErrorText className="mb-4">Wystąpił błąd podczas ładowania grupy</ErrorText>
            <Button
              variant="link"
              onPress={onRefresh}
            >
              <Text>Spróbuj ponownie</Text>
            </Button>
        </Container>
      )
    }

    // const renderUser = ({ item }: { item: User }) => (
    //     <View>
    //         <Text>{item.email}</Text>
    //     </View>
    // );

    return (
        // <View>
        //     <FlatList
        //         data={data}
        //         renderItem={renderUser}
        //         keyExtractor={(item) => item.id}
        //         refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
        //     />

        // </View>
        
        <Container>
            <View>
                <Text className={"text-4xl"}>{data.groupName}</Text>
                <Text>{data.description}</Text>
            </View>
            <TransactionsSection groupId={groupId} />
        </Container>
    );
}