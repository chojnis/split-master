import { StyleSheet, View, Text, FlatList, RefreshControl, Pressable } from 'react-native';
import { useGetGroupMembersQuery } from '~/api';
import { Group, User } from '~/api/types/entity';
import { useState } from 'react';
import { useLayoutEffect } from 'react';
import { Button } from 'react-native';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation';
import { StackNavigationProp } from '@react-navigation/stack';


type GroupDetailsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupDetails'>;
type GroupDetailsScreenRouteProp = RouteProp<GroupsStackParamList, 'GroupDetails'>;

export default function GroupDetails() {
    const router = useRoute<GroupDetailsScreenRouteProp>();
    const groupId = router.params.groupId;

    const { data, isLoading, error, refetch } = useGetGroupMembersQuery(groupId);
    const [refreshing, setRefreshing] = useState(false);
    const navigation = useNavigation<GroupDetailsStackNavigationProp>();

    const onRefresh = async () => {
        setRefreshing(true);
        await refetch();
        setRefreshing(false);
    };

    if (isLoading) return <Text>Loading...</Text>;
    if (error) return <Text>Error </Text>;

    const renderUser = ({ item }: { item: User }) => (
        <View>
            <Text>{item.email}</Text>
        </View>
    );

    return (
        <View>
            <FlatList
                data={data}
                renderItem={renderUser}
                keyExtractor={(item) => item.id}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
            />

        </View>
    );
}