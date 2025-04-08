import { Text } from '~/components/ui/text';
import { View, Pressable } from 'react-native';
import ReceiptText from '~/lib/icons/ReceiptText';
import { Currency } from '~/api/types/entity';
import { formatAmount } from '~/lib/utils';
import { formatDateNicely } from '~/lib/utils';
import Calendar from '~/lib/icons/Calendar';
import User from '~/lib/icons/User';

type TransactionItemProps = {
    id: string;
    payerName: string;
    payerImageUrl?: string;
    title: string;
    amount: number;
    currency: Currency;
    transactionDate: Date;
}
  
const TransactionItem = ({ id, payerName, payerImageUrl, title, amount, currency, transactionDate }: TransactionItemProps) => {
    return (
        <View className="flex flex-row items-center p-3 bg-gray-100 dark:bg-[#101828] rounded-lg">
            {/* <View className="mr-3 flex items-center justify-center"> 
                <ReceiptText className="dark:text-white text-black" width={24} height={24} />
            </View> */}

            <View className="flex flex-col items-start justify-start gap-1">
                <Text 
                    className="text-lg font-medium" 
                    numberOfLines={1}
                >
                    {title}
                </Text>

                <View className="flex flex-row items-center gap-2">
                    <User className="dark:text-white text-black" width={16} height={16} />
                    <Text 
                        numberOfLines={1}
                    >
                        {payerName}
                    </Text>
                </View>

                <View className="flex flex-row items-center gap-2">
                    <Calendar className="dark:text-white text-black" width={16} height={16} />
                    <Text>{formatDateNicely(transactionDate)}</Text>
                </View>

            </View>

            <View className="ml-auto">
                <Text className="text-xl font-medium">
                    {formatAmount(amount, currency.code)}
                </Text>
            </View>
        </View>
    );
}

export default TransactionItem;