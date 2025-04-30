import { View } from 'react-native';
import { useGetTransactionQuery, useGetPairExchangeRateQuery, useDeleteTransactionMutation } from '~/api';
import { useCallback, useState } from 'react';
import { Button } from '~/components/ui/button';	
import { Text } from '~/components/ui/text';
import { useNavigation, useRoute, RouteProp, useFocusEffect } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';
import { Container } from '~/components/Container';
import TransactionsSection from '~/components/group/TransactionSection';
import Loading from '~/components/Loading';
import ErrorText from '~/components/ErrorText';
import FloatingActionButton from '~/components/FloatingActionButton';
import SquarePen from '~/lib/icons/SquarePen';
import Settings from '~/lib/icons/Settings';
import { formatDateNicely, formatAmount } from '~/lib/utils';
import { Separator } from '~/components/Separator';
import Banknote from '~/lib/icons/Banknote';
import ArrowUp from '~/lib/icons/ArrowUp';
import ArrowDown from '~/lib/icons/ArrowDown';
import Calendar from '~/lib/icons/Calendar';
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '~/components/ui/accordion';
import Trash from '~/lib/icons/Trash';
import { showMessage } from 'react-native-flash-message';

type TransactionDetailsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'TransactionDetails'>;
type TransactionDetailsScreenRouteProp = RouteProp<GroupsStackParamList, 'TransactionDetails'>;

export default function TransactionDetails() {
    const router = useRoute<TransactionDetailsScreenRouteProp>();
    const {groupId, transactionId, defaultCurrency } = router.params;

    const { data, isLoading, isFetching, isError, refetch } = useGetTransactionQuery(transactionId);
    const { 
        data: exchangeRateData, 
        isLoading: isLoadingExchangeRate, 
        isError: isErrorExchangeRate,
        isSuccess: isSuccessExchangeRate
    } = useGetPairExchangeRateQuery(
        {from: data?.currency.id || '', to: defaultCurrency.id, date: data?.transactionDate ? new Date(data.transactionDate).toISOString().split('T')[0] : ''}, 
        {skip: !data || data.currency.id === defaultCurrency.id}
    );

    const [deleteTransaction, {isLoading: isLoadingDelete}] = useDeleteTransactionMutation();

    const [refreshing, setRefreshing] = useState(false);
    const navigation = useNavigation<TransactionDetailsStackNavigationProp>();

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

    const handleDeleteTransaction = async () => {
        try {
            const {data} = await deleteTransaction(transactionId);
            if (data) {
                navigation.goBack();
                showMessage({
                    message: 'Transakcja została usunięta',
                    type: 'success',
                });
            }
        } catch (error) {
            showMessage({
                message: 'Nie można usunąć transakcji. Spróbuj ponownie.',
                type: 'danger',
            });
        }
    };

    const showLoading = isFetching || isLoadingDelete;

    if (isLoading) return <Loading absolute reverseColors />;
    if (isError || !data) {
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

    return (
        <>
          {showLoading && <Loading absolute reverseColors />}
          <Container>
            <View className="flex flex-col gap-2">
                <Text className={"text-4xl"}>{data.name}</Text>
                <Separator />
                <View className="flex flex-row flex-wrap items-center justify-between gap-4">
                  <View className="flex flex-row items-center gap-2">
                    <Calendar className="dark:text-white text-black" width={30} height={30} />
                    <Text className={"text-xl"}>{formatDateNicely(data.transactionDate)}</Text>
                  </View>
                  <View className="flex flex-row items-center gap-2">
                    <Banknote className="dark:text-white text-black" width={30} height={30} />
                    <Text className={"text-xl"}>{formatAmount(data.originalAmount, data.currency.code)}</Text>
                  </View>
                </View>
            </View>

            <View className="flex flex-col gap-2 items-start justify-center mt-8 mb-4 w-full">
                <Text className="text-lg font-semibold">Zapłacone przez</Text>
                <View className="flex flex-row items-center gap-2 justify-between w-full p-4 bg-gray-100 dark:bg-[#101828] rounded-lg">
                    <Text className="text-lg">{data.payer.username || data.payer.email}</Text>
                    <View className="flex flex-row items-center gap-2 text-orange-500">
                      <ArrowDown className="text-orange-500" width={16} height={16} />
                      <Text className="text-lg text-orange-500">{formatAmount(data.originalAmount, data.currency.code)}</Text>
                    </View>
                </View>
            </View>
            <Separator />
            <View className="flex flex-col gap-2 items-start justify-center mt-4 mb-4">
                <Text className="text-lg font-semibold">Odbiorcy</Text>
                <View className="flex flex-col gap-2 items-center justify-center">
                    {data.entries.filter(entry => entry.type === 'DEBIT').map((entry) => (
                        <View key={entry.user.id} className="flex flex-row items-center gap-2 justify-between w-full p-4 bg-gray-100 dark:bg-[#101828] rounded-lg">
                            <Text className="text-lg">{entry.user.username || entry.user.email}</Text>
                            <View className="flex flex-row items-center justify-center gap-2 text-orange-500">
                              <ArrowUp className="text-green-500" width={16} height={16} />
                              <Text className="text-lg text-green-500">{formatAmount(entry.amount, defaultCurrency.code)}</Text>
                            </View>
                        </View>
                    ))}
                </View>
            </View>

            {defaultCurrency.id !== data.currency.id && (
              <Accordion
                type='single'
                collapsible
                className='w-full max-w-sm native:max-w-md'
              >
                <AccordionItem value='item-1'>
                  <AccordionTrigger>
                    <Text>Szczegóły</Text>
                  </AccordionTrigger>
                  <AccordionContent>
                    <Text>Ta transakcja jest w innej walucie niż domyślna waluta grupy, dlatego kwota zwrotu została przeliczona z kursem: {data.exchangeRate || exchangeRateData?.rate}</Text>
                  </AccordionContent>
                </AccordionItem>
              </Accordion>
            )}

          </Container>
          <FloatingActionButton 
            onPress={() => navigation.navigate('EditTransaction', { groupId, transactionId, defaultCurrency })}
            secondOnPress={handleDeleteTransaction}
            secondIcon={<Trash className="text-white" width={24} height={24} />}
            icon={<SquarePen className="text-white" width={24} height={24} />}
            secondClassName={"bg-red-500"}
            className={"bg-orange-500"}
          />
        </>
    );
}