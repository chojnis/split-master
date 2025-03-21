import { useState } from 'react';
import { View, TextInput, StyleSheet, Alert } from 'react-native';
import { useDispatch } from 'react-redux';
import { login as persistLogin } from '~/store/reducers/authReducer';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { AuthStackParamList } from '~/navigation';
import { Button } from '~/components/Button';
import { useLoginMutation } from '~/api';

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    padding: 20,
  },
  input: {
    height: 40,
    borderColor: 'gray',
    borderWidth: 1,
    marginBottom: 10,
    paddingHorizontal: 10,
  },
});

type LoginScreenNavigationProps = StackNavigationProp<AuthStackParamList, 'Login'>;

const Login = () => {

  const navigation = useNavigation<LoginScreenNavigationProps>();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [fetchLogin, {isLoading, error}] = useLoginMutation();
  const dispatch = useDispatch();

  const handleLogin = async () => {
    try {
      const { data, error: apiError } = await fetchLogin({ email, password });

      if (apiError) {
        Alert.alert('Login failed', 'An unexpected error occurred.');
        return;
      }

      // const responseData = await response.json();
      // const token = responseData.token;
      // const user = responseData.user;

       dispatch(persistLogin({ user: data.user, token: data.token }));
    } catch (error) {
      console.error('Login failed:', error);
      Alert.alert('Error', 'An unexpected error occurred.');
    }
  };

  return (
    <View style={styles.container}>
        <TextInput
            style={styles.input}
            placeholder="Email"
            value={email}
            onChangeText={setEmail}
        />
        <TextInput
            style={styles.input}
            placeholder="Password"
            value={password}
            onChangeText={setPassword}
            secureTextEntry
        />
      <Button title="Zaloguj się" onPress={handleLogin} />
      <Button title="Nie masz konta?" link={true} onPress={() => navigation.navigate('Register')} />
    </View>
  );
};

export default Login;
