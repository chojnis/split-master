import { View } from "react-native";
import { Container } from "~/components/Container";
import ErrorText from "~/components/ErrorText";
import { Button } from "~/components/ui/button";
import { Text } from "~/components/ui/text";

type ErrorProps = {
    message: string;
    onRefresh?: () => void;
    className?: string;
}

const Error = ({ onRefresh, message, className }: ErrorProps) => {

    // message ||= "Wystąpił błąd.";

    return (
        <View className={`${className || ''}`}>
            <ErrorText className="mb-2">{message}</ErrorText>
            {onRefresh && (
                <Button
                    variant="link"
                    onPress={onRefresh}
                >
                    <Text>Spróbuj ponownie</Text>
                </Button>
            )}

        </View>
    )
}

export default Error;