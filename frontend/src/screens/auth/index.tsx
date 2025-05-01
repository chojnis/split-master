import { useNavigation } from '@react-navigation/native';
import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';
import { StackNavigationProp } from '@react-navigation/stack';
import { AuthStackParamList } from '~/navigation/auth';
import { Container } from '~/components/Container';

type WelcomeScreenNavigationProps = StackNavigationProp<AuthStackParamList, 'Welcome'>;

/**
 * Welcome screen component for the application.
 * 
 * This component serves as the entry point of the authentication flow,
 * displaying the application title, author information, and a button to
 * navigate to the login screen.
 * 
 */
export default function Welcome() {
    const navigation = useNavigation<WelcomeScreenNavigationProps>();

    return (
        <Container>
            <Text className={'text-4xl text-center'}>
                Aplikacja mobilna do dzielenia kosztów
            </Text>
            <Text className={'text-lg mt-2 text-center text-gray-500'}>
                Daniel Chojnicki, numer indeksu 308034
            </Text>
            <Button
                className={'mt-4'} 
                onPress={() => navigation.navigate('Login')}
            >
                <Text>Logowanie</Text>
            </Button>
        </Container>
    );
}