import { Alert } from 'react-native';
import { StackNavigationProp } from '@react-navigation/stack';
import { useFocusEffect, useNavigation } from '@react-navigation/native';
import { Container } from '~/components/Container';
import Form, { FormDataType, FormFieldType } from '~/components/form/Form';
import { useAddGroupMutation, useGetCurrenciesQuery } from '~/api';
import { GroupsStackParamList } from '~/navigation/groups';
import { useCallback, useEffect, useState } from 'react';
import Loading from '~/components/Loading';
import { Currency } from '~/api/types/entity';

type AddGroupScreenNavigationProps = StackNavigationProp<GroupsStackParamList, 'AddGroup'>;

const AddGroup = () => {
    const navigation = useNavigation<AddGroupScreenNavigationProps>();
    const [fetchAddGroup, {isLoading, error}] = useAddGroupMutation();
    const { 
        data: currencies, 
        error: currenciesError, 
        isLoading: isLoadingCurrencies, 
        isFetching: isFetchingCurrencies,
        isSuccess: isSuccessCurrencies,
        isError: isErrorCurrencies,
        refetch: refetchCurrencies 
    } = useGetCurrenciesQuery();

    const [fields, setFields] = useState<FormFieldType[]>([]);

    useFocusEffect(
        useCallback(() => {
            refetchCurrencies();
        }, [refetchCurrencies])
    );

    useEffect(() => {
        if (isFetchingCurrencies) return;

        if (
            isErrorCurrencies 
            || !isSuccessCurrencies
            || currencies.length === 0
        ) {
            Alert.alert('Błąd', 'Nie można pobrać walut. Spróbuj ponownie.');
            navigation.goBack();
            return;
        }

        setFields([
            { label: 'Nazwa grupy', placeholder: 'Wakacje we Włoszech', name: 'groupName', type: 'text', required: true },
            { label: 'Opis', placeholder: 'Opłaty na życie', name: 'description', type: 'textarea' },
            {
                label: 'Waluta rozliczeń',
                name: 'currencyId',
                type: 'select',
                required: true,
                selectOptions: currencies.map((currency: Currency) => ({ label: currency.name, value: currency.id })),
                defaultSelectValue: {label: currencies[0].name, value: currencies[0].id},
            }
        ]);
    }, [
        currencies,
        isFetchingCurrencies,
        isErrorCurrencies,
        isSuccessCurrencies
    ]);

    const handleSubmit = async (formData: FormDataType) => {
        const { groupName, description, currencyId } = formData as { groupName: string; description: string, currencyId: string };

        try{
            const { data } = await fetchAddGroup({ groupName, description, currencyId });

            if(data) {
                navigation.goBack();
            }
        } catch (err) {
            Alert.alert('Błąd', 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie.');
        }
    };

    if(fields.length === 0) return <Loading absolute reverseColors />;

    return (
        <Container>
            <Form fields={fields} onSubmit={handleSubmit} isLoading={isLoading} error={error} submitText="Dodaj grupę" />
        </Container>
    );
}

export default AddGroup;