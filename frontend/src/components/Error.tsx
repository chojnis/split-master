import { Container } from "~/components/Container";
import ErrorText from "~/components/ErrorText";
import { Button } from "~/components/ui/button";
import { Text } from "~/components/ui/text";

type ErrorProps = {
    onRefresh: () => void;
    message?: string;
}

const Error = ({ onRefresh, message }: ErrorProps) => {

    message ||= "Wystąpił błąd podczas ładowania grup";

    return (
        <Container>
        <ErrorText className="mb-4">{message}</ErrorText>
            <Button
            variant="link"
            onPress={onRefresh}
            >
            <Text>Spróbuj ponownie</Text>
            </Button>
        </Container>
    )
}

export default Error;