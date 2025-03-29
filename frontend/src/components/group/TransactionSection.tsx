import { useGetGroupTransactionsQuery } from '~/api';
import { Currency, Transaction, User } from '~/api/types/entity';
import TransactionItem from '~/components/transaction/TransactionItem';
import { View, FlatList } from 'react-native';
import { Text } from '~/components/ui/text';

const TransactionsSection = ({ groupId }: {groupId: string}) => {
    // const { data, isLoading, error } = useGetGroupTransactionsQuery(groupId);

    // if (isLoading) return <Text>Loading...</Text>;
    // if (error) return <Text>Error </Text>;

    const currency: Currency = {
        id: '1',
        code: 'PLN',
        symbol: 'zł',
    };

    const user: User = {
        id: '1',
        email: "psikuta@gmail.com",
        username: "psikuta",
    }

    const dummyData: Transaction[] = [
        { id: '1', name: 'Transaction 1', currency: currency, created_at: new Date(), payer: user, payees: [user], amount: 100 },
        { id: '2', name: 'Transaction 2', currency: currency, created_at: new Date(), payer: user, payees: [user], amount: 200 },
        { id: '3', name: 'Transaction 3', currency: currency, created_at: new Date(), payer: user, payees: [user], amount: 300 },
    ];

    const renderTransaction = ({ item }: { item: Transaction }) => (
        <TransactionItem
            payerName={item.payer.username || item.payer.email}
            title={item.name}
            amount={item.amount}
            currencySymbol={item.currency.symbol}
        />
    );

    return (
        <View className="mt-4">
            <View>
                <Text className="text-md uppercase">Transakcje</Text>
            </View>
            <FlatList
                data={dummyData}
                renderItem={renderTransaction}
                keyExtractor={(item) => item.id}
            />
        </View>
    );
}

export default TransactionsSection;