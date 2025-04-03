import { useGetGroupTransactionsQuery } from '~/api';
import { Currency, Transaction, User } from '~/api/types/entity';
import TransactionItem from '~/components/transaction/TransactionItem';
import { View, FlatList, RefreshControl } from 'react-native';
import { Text } from '~/components/ui/text';
import { useCallback, useState } from 'react';
import Loading from '~/components/Loading';
import Error from '~/components/Error';
import { useFocusEffect } from '@react-navigation/native';

const TransactionsSection = ({ groupId }: {groupId: string}) => {
    const { data, isLoading, refetch, error } = useGetGroupTransactionsQuery(groupId);
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

    const renderTransaction = ({ item }: { item: Transaction }) => (
        <TransactionItem
            id={item.id}
            payerName={item.payer.username || item.payer.email}
            title={item.name}
            amount={item.amount}
            currencySymbol={item.currency.name}
        />
    );
    
    const showLoading = isLoading || refreshing;
    if(showLoading && error) return <Loading reverseColors />

    return (
        <View className="mt-4">
            <View className="flex items-start justify-center mb-4">
                <Text className="text-lg uppercase">Transakcje</Text>
            </View>
            {showLoading && <Loading absolute reverseColors />}
            {error ? (
                <Error onRefresh={onRefresh} message="Wystąpił błąd podczas ładowania grup" />
            )  : (
                <FlatList
                    data={data}
                    renderItem={renderTransaction}
                    keyExtractor={(item) => item.id}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
                />
            )}
        </View>
    );
}

export default TransactionsSection;