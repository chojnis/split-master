import { Text } from '~/components/ui/text';
import { View, Pressable } from 'react-native';
import ReceiptText from '~/lib/icons/ReceiptText';

type TransactionItemProps = {
    id: string;
    payerName: string;
    payerImageUrl?: string;
    title: string;
    amount: number;
    currencySymbol: string;
}
  
const TransactionItem = ({ id, payerName, payerImageUrl, title, amount, currencySymbol }: TransactionItemProps) => {
    return (
        <View className="flex flex-row justify-between items-center p-3 rounded-md bg-stone-100 dark:bg-stone-800 mb-2">
            <View className="mr-3 flex items-center justify-center"> 
                <ReceiptText className="dark:text-white text-black" width={24} height={24} />
            </View>

            <View className="flex-1">
                <Text 
                    className="text-base font-medium" 
                    numberOfLines={1}
                >
                    {title}
                </Text>
                <Text 
                    className="text-sm mt-1" 
                    numberOfLines={1}
                >
                    {payerName}
                </Text>
            </View>

            <View className="ml-3">
                <Text className="text-xl font-medium">
                    {amount.toFixed(2).replace('.', ',')} {currencySymbol}
                </Text>
            </View>
        </View>
    );
}

export default TransactionItem;