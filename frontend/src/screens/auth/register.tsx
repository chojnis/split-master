import React, { useState } from 'react';
import { View, TextInput, StyleSheet, Alert } from 'react-native';
import { useDispatch } from 'react-redux';
import { login } from '~/store/reducers/authReducer';
import { Button } from '~/components/Button';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import Navigation, { AuthStackParamList } from '~/navigation';

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

type RegisterScreenNavigationProps = StackNavigationProp<AuthStackParamList, 'Register'>;

const Register = () => {
  const navigation = useNavigation<RegisterScreenNavigationProps>();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const dispatch = useDispatch();

  const handleRegister = async () => {
    try {
      const response = await fetch('https://a8aa-217-97-63-46.ngrok-free.app/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/ld+json',
        },
        body: JSON.stringify({ email, plainPassword: password }),
      });

      if (!response.ok) {
        if (response.status === 401) {
          Alert.alert('Login failed', 'Incorrect email or password');
        } else {
          Alert.alert('Error', `HTTP error! status: ${response.status}`);
        }
        return;
      }

      // const responseData = await response.json();
      // const token = responseData.token;
      // const user = responseData.user;

      // dispatch(login({ user, token }));
      navigation.goBack();
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
      <Button title="Zarejestruj się" onPress={handleRegister} />
    </View>
  );
};

export default Register;
