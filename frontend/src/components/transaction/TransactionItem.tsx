import { Text } from '~/components/ui/text';
import { View } from 'react-native';
import UserAvatar from '../user/UserAvatar';

type TransactionItemProps = {
    payerName: string;
    payerImageUrl?: string;
    title: string;
    amount: number;
    currencySymbol: string;
}
  
const TransactionItem = ({ payerName, payerImageUrl, title, amount, currencySymbol }: TransactionItemProps) => {
    return (
        <View className="flex flex-row justify-between items-center p-3 border-b border-gray-200">
            <View className="mr-3 flex items-center justify-center"> 
                <UserAvatar
                    userName={payerName}
                    imageUrl={payerImageUrl}
                />
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
                <Text className="text-base font-medium">
                    {amount} {currencySymbol}
                </Text>
            </View>
        </View>
    );
}

export default TransactionItem;