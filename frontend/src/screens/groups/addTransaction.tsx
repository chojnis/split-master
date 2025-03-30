import { Alert } from 'react-native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RouteProp, useNavigation, useRoute } from '@react-navigation/native';
import { Container } from '~/components/Container';
import Form, { FormDataType, FormFieldType } from '~/components/form/Form';
import { useAddTransactionMutation, useGetCurrenciesQuery, useGetGroupMembersQuery } from '~/api';
import { GroupsStackParamList } from '~/navigation/groups';
import { Currency } from '~/api/types/entity';

type AddTransactionScreenNavigationProps = StackNavigationProp<GroupsStackParamList, 'AddTransaction'>;
type AddTransactionScreenRouteProps = RouteProp<GroupsStackParamList, 'AddTransaction'>;

const AddTransaction = () => {
    const navigation = useNavigation<AddTransactionScreenNavigationProps>();
    const route = useRoute<AddTransactionScreenRouteProps>();

    const { groupId } = route.params;

    const [fetchAddTransaction, {isLoading, error}] = useAddTransactionMutation();
    const { data: currencies, error: currenciesError } = useGetCurrenciesQuery();
    const { data: members, error: membersError } = useGetGroupMembersQuery(groupId);

    if(currenciesError || membersError) {
        Alert.alert('Błąd', 'Nie można pobrać walut lub członków grupy. Spróbuj ponownie.');
        navigation.goBack();
    }

    const selectOptions = currencies?.map((currency: Currency) => ({
      label: currency.name,
      value: currency.id,
    })) || [];

    const selectMembers = members?.map((member) => ({
        label: member.username || member.email,
        value: member.id,
    })) || [];

    const handleSubmit = async (formData: FormDataType) => {
        const { name, amount, currencyId, payerId, payeesIds } = formData as { name: string; amount: string, currencyId: string, payerId: string, payeesIds: string[] };

        console.log(payeesIds);
        try{
            const { data, error} = await fetchAddTransaction({ groupId, data: { name, amount, currencyId, payerId, payeesIds} });
            if(data) {
                navigation.goBack();
            }
        } catch (err) {
            Alert.alert('Błąd', 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie.');
        }
    };

    const fields = [
        { label: 'Nazwa transakcji', placeholder: 'Pączki', name: 'name', type: 'text', required: true } as FormFieldType,
        { label: 'Wartość transakcji', placeholder: '0.00', name: 'amount', type: 'number', width: 50, required: true } as FormFieldType,
        { label: 'Waluta', name: 'currencyId', type: 'select', width: 50, required: true, selectOptions: selectOptions, defaultSelectValue: selectOptions.at(0)} as FormFieldType,
        { label: 'Płatnik', name: 'payerId', type: 'select', selectOptions: selectMembers, required: true, defaultSelectValue: selectMembers.at(0)} as FormFieldType,
        { label: 'Odbiorcy', name: 'payeesIds', type: 'select', selectOptions: selectMembers, required: true, defaultSelectValue: [selectMembers.at(0)], multiple: true } as FormFieldType,
    ];
    
    return (
        <Container>
            <Form fields={fields} onSubmit={handleSubmit} isLoading={isLoading} error={error} submitText="Dodaj transakcję" />
        </Container>
    );
}

export default AddTransaction;