import { Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { AuthStackParamList } from '~/navigation/auth';
import { useRegisterMutation } from '~/api';
import Form, { FormFieldType, FormDataType } from '~/components/form/Form';
import { Container } from '~/components/Container';

type RegisterScreenNavigationProps = StackNavigationProp<AuthStackParamList, 'Register'>;

const Register = () => {
  const navigation = useNavigation<RegisterScreenNavigationProps>();
  const [fetchRegister, {isLoading, error}] = useRegisterMutation();
  
  const handleRegister = async (formData: FormDataType) => {
    const { email, plainPassword } = formData as { email: string; plainPassword: string };

    try {
      const { data } = await fetchRegister({ email, plainPassword });

      if(data){
        navigation.goBack();
      }
    } catch (err) {
      Alert.alert('Błąd', 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie.');
    }
  };

  const fields = [
    { label: 'E-mail', placeholder: 'user@example.com', name: 'email', type: 'text', required: true } as FormFieldType,
    { label: 'Hasło', placeholder: 'hasło', name: 'plainPassword', type: 'password', required: true } as FormFieldType,
  ]

  return (
    <Container>
      <Form fields={fields} onSubmit={handleRegister} error={error} submitText="Zarejestruj się" isLoading={isLoading} />
    </Container>
  );
};

export default Register;
