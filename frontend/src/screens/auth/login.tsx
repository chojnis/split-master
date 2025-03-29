import { useState } from 'react';
import { View, StyleSheet, Alert } from 'react-native';
import { Text } from '~/components/ui/text';
import { useDispatch } from 'react-redux';
import { login as persistLogin } from '~/store/reducers/authReducer';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { AuthStackParamList } from '~/navigation/auth';
import { Button } from '~/components/ui/button';
import { useLoginMutation } from '~/api';
import Form, { FormFieldType, FormDataType } from '~/components/form/Form';
import { Container } from '~/components/Container';

type LoginScreenNavigationProps = StackNavigationProp<AuthStackParamList, 'Login'>;

const Login = () => {
  const navigation = useNavigation<LoginScreenNavigationProps>();
  const [fetchLogin, {isLoading, error}] = useLoginMutation();
  const dispatch = useDispatch();

  const handleLogin = async (formData: FormDataType) => {
      const { email, password } = formData as { email: string; password: string };
  
      try {
        const { data } = await fetchLogin({ email, password });
  
        if(data) {
          dispatch(persistLogin({ user: data.user, token: data.token, refresh_token: data.refresh_token }));
        }
  
      } catch (err) {
        Alert.alert('Błąd', 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie.');
      }
    };

  const fields = [
      { label: 'E-mail', placeholder: 'user@example.com', name: 'email', type: 'text', required: true } as FormFieldType,
      { label: 'Hasło', placeholder: '*****', name: 'password', type: 'password', required: true } as FormFieldType,
  ];

  return (
    <Container>
      <Form fields={fields} onSubmit={handleLogin} error={error} submitText="Zaloguj się" isLoading={isLoading} />
      <Button variant={null} onPress={() => navigation.navigate('Register')}>
        <Text>Nie masz konta?</Text>
      </Button>
    </Container>
  );
};

export default Login;
