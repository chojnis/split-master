import { Text } from '~/components/ui/text';
import { View, Pressable } from 'react-native';
import HandCoins from '~/lib/icons/HandCoins';
import Banknote from '~/lib/icons/Banknote';
import MoveRight from '~/lib/icons/MoveRight';
import { Currency, User } from '~/api/types/entity';
import { formatAmount } from '~/lib/utils';
import { Move } from 'lucide-react-native';
import { Button } from '../ui/button';
import { useAddTransactionMutation } from '~/api';
import { AddTransactionRequest } from '~/api/types/request';
import { showMessage } from 'react-native-flash-message';
import Loading from '../Loading';

type SettlementItemProps = {
    groupId: string;
    from: User;
    to: User;
    amount: number;
    currency: Currency;
    onSettlement: () => void;
}
  
const SettlementItem = ({ from, to, amount, currency, groupId, onSettlement }: SettlementItemProps) => {
    const [addTransaction, {isLoading: isLoading}] = useAddTransactionMutation();

    const handleSettlement = async (from: User, to: User, amount: number, currency: Currency) => {
        const transaction = {
            payerId: from.id,
            amount: amount,
            currencyId: currency.id,
            name: `Rozliczenie`,
            payeesIds: [to.id]
        } as AddTransactionRequest;

        try {
            const { data, error } = await addTransaction({ groupId, data: transaction});

            if (error) {
                showMessage({
                    message: 'Nie udało się dodać rozliczenia. Spróbuj ponownie.',
                    type: 'danger',
                });
                return;
            }
            showMessage({
                message: 'Rozliczenie zostało dodane pomyślnie.',
                type: 'success',
            });
            onSettlement();
        } catch (error) {
            showMessage({
                message: 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie.',
                type: 'danger',
            });
        }
    }

    return (
        <>
        {isLoading && <Loading absolute reverseColors />}
        <View className="flex flex-3 justify-between items-center p-3 bg-gray-100 dark:bg-[#101828] rounded-lg mb-2 w-full">
            <View className="flex flex-row items-center justify-between w-full">
                <Text className="flex-1 text-sm text-center">{from.username || from.email}</Text>
                <View className="flex-1 flex flex-col items-center justify-center gap-2">
                    <Text className="text-xl font-semibold text-emerald-500">{formatAmount(amount, currency.code)}</Text>
                    {/* <View className="flex flex-row items-center justify-center"> */}
                        {/* <Banknote className="dark:text-white text-black" width={24} height={24} /> */}
                        {/* <MoveRight className="text-emerald-500" width={24} height={24} /> */}
                        <Button 
                            variant="outline" 
                            className="dark:bg-transparent" 
                            size="sm"
                            onPress={() => {
                                handleSettlement(from, to, amount, currency);
                            }}
                        >
                            <Text className="text-sm">Rozlicz</Text>
                        </Button>
                    {/* </View> */}
                </View>
                <Text className="flex-1 text-sm text-center">{to.username || to.email}</Text>
            </View>
        </View>
        </>
    );
}

export default SettlementItem;