import { Alert } from 'react-native';
import { StackNavigationProp } from '@react-navigation/stack';
import { useNavigation } from '@react-navigation/native';
import { Container } from '~/components/Container';
import Form, { FormDataType, FormFieldType } from '~/components/form/Form';
import { useAddGroupMutation } from '~/api';
import { GroupsStackParamList } from '~/navigation/groups';

type AddGroupScreenNavigationProps = StackNavigationProp<GroupsStackParamList, 'AddGroup'>;

const AddGroup = () => {
    const navigation = useNavigation<AddGroupScreenNavigationProps>();
    const [fetchAddGroup, {isLoading, error}] = useAddGroupMutation();
    const handleSubmit = async (formData: FormDataType) => {
        const { groupName, description } = formData as { groupName: string; description: string };

        try{
            const { data } = await fetchAddGroup({ groupName, description });

            if(data) {
                navigation.goBack();
            }
        } catch (err) {
            Alert.alert('Błąd', 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie.');
        }
    };

    const fields = [
        { label: 'Nazwa grupy', placeholder: 'Wakacje we Włoszech', name: 'groupName', type: 'text', required: true } as FormFieldType,
        { label: 'Opis', placeholder: 'Opłaty na życie', name: 'description', type: 'textarea' } as FormFieldType,
    ];
    
    return (
        <Container>
            <Form fields={fields} onSubmit={handleSubmit} isLoading={isLoading} error={error} submitText="Dodaj grupę" />
        </Container>
    );
}

export default AddGroup;