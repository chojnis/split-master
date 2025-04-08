import { useGetGroupSettlementsQuery } from '~/api';
import { GroupSettlement } from '~/api/types/entity';
import SettlementItem from '~/components/group/SettlementItem';
import { View, FlatList, RefreshControl } from 'react-native';
import { Text } from '~/components/ui/text';
import { useCallback, useState } from 'react';
import Loading from '~/components/Loading';
import Error from '~/components/Error';
import { useFocusEffect } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';

type GroupDetailsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupDetails'>;

type SettlementsSectionProps = {
    groupId: string;
    navigation: GroupDetailsStackNavigationProp
}

const SettlementsSection = ({ groupId, navigation }: SettlementsSectionProps) => {
    const { data, isLoading, isSuccess, refetch, error } = useGetGroupSettlementsQuery(groupId);
    const [refreshing, setRefreshing] = useState(false);

    const onRefresh = async () => {
        setRefreshing(true);
        await refetch();
        setRefreshing(false);
    };

    useFocusEffect(
        useCallback(() => {
            refetch();
        }, [])
    );

    const onSettlement = () => {
        refetch();
    }

    const renderSettlement = ({ item }: { item: GroupSettlement }) => (
        <SettlementItem
            groupId={groupId}
            from={item.from}
            to={item.to}
            amount={item.amount}
            currency={item.currency}
            onSettlement={onSettlement}
        />
    );
    
    const showLoading = isLoading || refreshing;
    if(showLoading && error) return <Loading reverseColors />

    return (
        <View className="mt-4">
            {showLoading && <Loading absolute reverseColors />}
            {error ? (
                <Error onRefresh={onRefresh} message="Wystąpił błąd podczas ładowania grup" />
            )  : (
                <FlatList
                    data={data}
                    renderItem={renderSettlement}
                    keyExtractor={(item) => item.from.id + item.to.id}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
                    className="flex"
                    ListEmptyComponent={() => (
                        isSuccess && (
                            <View className="flex items-center justify-center">
                                <Text className="dark:text-gray-300 text-gray-500">Jesteśmy na czysto</Text>
                            </View>
                        )
                    )}
                    contentContainerStyle={{ paddingBottom: 96 }}
                    ListFooterComponent={() => (
                        <View className="h-24"></View>
                    )}
                />
            )}
        </View>
    );
}

export default SettlementsSection;