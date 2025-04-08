import { useGetGroupTransactionsQuery, useLazyGetGroupTransactionsQuery } from '~/api';
import { Currency, Transaction, User } from '~/api/types/entity';
import TransactionItem from '~/components/transaction/TransactionItem';
import { View, FlatList, RefreshControl, Pressable } from 'react-native';
import { Text } from '~/components/ui/text';
import { useCallback, useEffect, useState } from 'react';
import Loading from '~/components/Loading';
import Error from '~/components/Error';
import { useFocusEffect } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';

type GroupDetailsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupDetails'>;

type TransactionSectionProps = {
    groupId: string;
    defaultCurrency: Currency;
    navigation: GroupDetailsStackNavigationProp
}

const TransactionsSection = ({ groupId, defaultCurrency, navigation }: TransactionSectionProps) => {
    // const { data, isLoading, refetch, error } = useGetGroupTransactionsQuery({groupId});

    const [transactions, setTransactions ] = useState<Transaction[]>([]);
    const [page, setPage ] = useState(1);
    const [hasMore, setHasMore] = useState(true);
    const [trigger, { isLoading, isFetching, isSuccess, isError }] = useLazyGetGroupTransactionsQuery();
    
    const [refreshing, setRefreshing] = useState(false);

    const loadInitPage = async () => {
        const { data, isSuccess } = await trigger({groupId, page: 1});
        setTransactions(isSuccess ? data : []);
        setPage(isSuccess ? 2 : 1);
        setHasMore(isSuccess ? data.length !== 0 : true);
      }
    
      const loadNextPage = async (manual?: boolean) => {
        if(!hasMore || isFetching || (!manual && isError)) return;
        if(page === 1) return loadInitPage();
        const { data, isSuccess } = await trigger({groupId, page});
        if(isSuccess) {
            setTransactions((prev) => [...prev, ...data]);
            setPage((prev) => prev + 1);
            if(data.length === 0) {
                setHasMore(false);
            }
        }
      }

    const onRefresh = async () => {
        setRefreshing(true);
        // await refetch();
        await loadInitPage();
        setRefreshing(false);
    };

    useEffect(() => {
        loadInitPage();
    }, []);
    
    useFocusEffect(
        useCallback(() => {
            // refetch();
            loadInitPage();
        }, [])
    );

    const renderTransaction = ({ item, index }: { item: Transaction, index: number }) => (
        // <View>
            <Pressable
                onPress={() => {
                    navigation.navigate("TransactionDetails", {
                        transactionId: item.id,
                        groupId,
                        defaultCurrency
                    });
                }}
                className={`${index > 0 ? 'mt-2' : ''}`}
            >
                <TransactionItem
                    id={item.id}
                    payerName={item.payer.username || item.payer.email}
                    title={item.name}
                    amount={item.originalAmount}
                    currency={item.currency}
                    transactionDate={item.transactionDate}
                />
            </Pressable>
        // </View>
    );
    
    // const showLoading = isLoading || refreshing;
    const showLoading = isLoading || refreshing || (transactions.length === 0 && isFetching);
    // if(showLoading && error) return <Loading reverseColors />
    return (
        <View className="mt-4">
            {showLoading && <Loading absolute reverseColors />}
            {/* {error ? (
                <Error onRefresh={onRefresh} message="Wystąpił błąd podczas ładowania grup" />
            )  : ( */}
                <FlatList
                    // data={data}
                    data={transactions}
                    renderItem={renderTransaction}
                    keyExtractor={(item) => `transaction-${item.id}`}
                    onEndReached={()=>loadNextPage()}
                    onEndReachedThreshold={0.5}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
                    className="flex"
                    ListEmptyComponent={() => (
                        isSuccess && (
                            <View className="flex items-center justify-center">
                                <Text className="dark:text-gray-300 text-gray-500">Nie ma jeszcze transakcji</Text>
                            </View>
                        )
                    )}
                    contentContainerStyle={{ paddingBottom: 96 }}
                    ListFooterComponent={() => (
                        <>
                            {isError && !isFetching && (
                                <Error className="mt-4" onRefresh={()=>loadNextPage(true)} message="Wystąpił błąd podczas ładowania transakcji" />
                            )}
                            <View className="h-24"></View>
                        </>
                    )}
                />
            {/* )} */}
        </View>
    );
}

export default TransactionsSection;