import { Text, View } from 'react-native'

type ErrorTextProps = {
    children: string;
    className?: string;
}

const ErrorText = ({ children, className }: ErrorTextProps) => {
    return (
        <View className={"mb-4 p-3 bg-red-100 rounded " + (className ? ` ${className}` : '')}>
            <Text className="text-red-700">{children}</Text>
        </View>
    )
}

export default ErrorText