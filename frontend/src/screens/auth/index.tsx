import { useNavigation } from '@react-navigation/native';
import { StyleSheet, View } from 'react-native';
import { Button } from '~/components/Button';
import { StackNavigationProp } from '@react-navigation/stack';
import { AuthStackParamList } from '~/navigation';

type WelcomeScreenNavigationProps = StackNavigationProp<AuthStackParamList, 'Welcome'>;

export default function Welcome() {
    const navigation = useNavigation<WelcomeScreenNavigationProps>();

    return (
        <View style={styles.container}>
            <Button onPress={() => navigation.navigate('Login')} title="Zaloguj się" />
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        padding: 6,
    },
});