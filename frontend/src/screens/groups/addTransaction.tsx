import { Alert } from 'react-native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RouteProp, useFocusEffect, useNavigation, useRoute } from '@react-navigation/native';
import { Container } from '~/components/Container';
import Form, { FormDataType, FormFieldType } from '~/components/form/Form';
import { useAddTransactionMutation, useGetCurrenciesQuery, useGetGroupMembersQuery, useGetPairExchangeRateQuery } from '~/api';
import { GroupsStackParamList } from '~/navigation/groups';
import { Currency } from '~/api/types/entity';
import Loading from '~/components/Loading';
import { useCallback, useEffect, useState } from 'react';

type AddTransactionScreenNavigationProps = StackNavigationProp<GroupsStackParamList, 'AddTransaction'>;
type AddTransactionScreenRouteProps = RouteProp<GroupsStackParamList, 'AddTransaction'>;

const AddTransaction = () => {
    const navigation = useNavigation<AddTransactionScreenNavigationProps>();
    const route = useRoute<AddTransactionScreenRouteProps>();
    const { groupId, defaultCurrencyId } = route.params;

    const [fetchAddTransaction, { isLoading, error }] = useAddTransactionMutation();
    const { 
        data: currencies, 
        isError: isErrorCurrencies, 
        isLoading: isLoadingCurrencies,
        isFetching: isFetchingCurrencies,
        isSuccess: isSuccessCurrencies,
        refetch: refetchCurrencies 
    } = useGetCurrenciesQuery();
    const { 
        data: members, 
        isError: isErrorMembers, 
        isLoading: isLoadingMembers, 
        isFetching: isFetchingMembers,
        isSuccess: isSuccessMembers, 
        refetch: refetchMembers 
    } = useGetGroupMembersQuery(groupId);

    const [selectedCurrencyId, setSelectedCurrencyId] = useState<string>("");

    const { 
        data: exchangeRateData, 
        isLoading: isLoadingExchangeRate, 
        isError: isErrorExchangeRate,
        isSuccess: isSuccessExchangeRate,
        isFetching: isFetchingExchangeRate,
        refetch: refetchExchangeRate 
    } = useGetPairExchangeRateQuery(
        {from: selectedCurrencyId, to: defaultCurrencyId}, 
        {skip: !selectedCurrencyId || !defaultCurrencyId || selectedCurrencyId === defaultCurrencyId || selectedCurrencyId === ""}
    );

    const [fields, setFields] = useState<FormFieldType[]>([]);

    useFocusEffect(
        useCallback(() => {
            refetchCurrencies();
            refetchMembers();
        }, [refetchCurrencies, refetchMembers])
    );

    useEffect(() => {
        if (isFetchingCurrencies || isFetchingMembers) return;

        if (
            isErrorCurrencies 
            || isErrorMembers
            || !isSuccessCurrencies
            || !isSuccessMembers
            || currencies.length === 0
            || members.length === 0
        ) {
            Alert.alert('Błąd', 'Nie można pobrać walut lub członków grupy. Spróbuj ponownie.');
            navigation.goBack();
            return;
        }

        setFields([
            { label: 'Nazwa transakcji', placeholder: 'Pączki', name: 'name', type: 'text', required: true },
            { label: 'Wartość transakcji', placeholder: '0.00', name: 'amount', type: 'number', width: 70, required: true },
            {
                label: 'Waluta',
                name: 'currencyId',
                type: 'select',
                width: 30,
                required: true,
                selectOptions: currencies.map((currency: Currency) => ({ label: currency.name, value: currency.id })),
                // defaultSelectValue: {label: currencies[0].name, value: currencies[0].id},
                value: currencies[0].id,
            },
            {
                label: 'Płatnik',
                name: 'payerId',
                type: 'select',
                required: true,
                selectOptions: members.map((member) => ({ label: member.username || member.email, value: member.id })),
                // defaultSelectValue: {label: members[0].username || members[0].email, value: members[0].id},
                value: members[0].id,
            },
            {
                label: 'Odbiorcy',
                name: 'payeesIds',
                type: 'select',
                required: true,
                multiple: true,
                selectOptions: members.map((member) => ({ label: member.username || member.email, value: member.id })),
                // defaultSelectValue: [{label: members[0].username || members[0].email, value: members[0].id}],
                value: [members[0].id],
            },
        ]);

        setSelectedCurrencyId(currencies[0].id);
    }, [
        currencies, 
        members,
        isFetchingCurrencies,
        isFetchingMembers,
        isErrorCurrencies,
        isErrorMembers,
        isSuccessCurrencies,
        isSuccessMembers
    ]);

    useEffect(() => {
        let placeholder = '';
        if (
            !isFetchingExchangeRate
            && !isErrorExchangeRate
            && isSuccessExchangeRate 
        ) {
            placeholder = '1 ' + exchangeRateData.fromCurrency + ' = ' + exchangeRateData.rate.toString() + ' ' + exchangeRateData.toCurrency;
        }
    
        setFields((prevFields) => 
            prevFields.map((field) => 
                field.name === 'exchangeRate' 
                ? { ...field, placeholder: placeholder }
                : field
            )
        );
    }, [
        isFetchingExchangeRate, 
        isErrorExchangeRate, 
        isSuccessExchangeRate,
        exchangeRateData,
        selectedCurrencyId,
    ]);

    const handleSubmit = async (formData: FormDataType) => {
        const { name, amount, currencyId, payerId, payeesIds } = formData as { name: string; amount: string; currencyId: string; payerId: string; payeesIds: string[] };
        try {
            const { data } = await fetchAddTransaction({ groupId, data: { name, amount, currencyId, payerId, payeesIds } });
            if (data) {
                navigation.goBack();
            }
        } catch (err) {
            Alert.alert('Błąd', 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie.');
        }
    };

    const addExchangeRateField = () => {
        const exchangeRateField: FormFieldType = {
            label: 'Kurs wymiany',
            name: 'exchangeRate',
            type: 'number'
        }

        setFields((prevFields) => {
            if (prevFields.some((field) => field.name === 'exchangeRate')) return prevFields;
            return [
                ...prevFields.slice(0, 3),
                exchangeRateField,
                ...prevFields.slice(3),
            ];
        });
    };

    const removeExchangeRateField = () => {
        setFields((prevFields) => prevFields.filter((field) => field.name !== 'exchangeRate'));
    };

    const onChange = (data: FormDataType) => {
        const { currencyId } = data as { currencyId: string };
        setSelectedCurrencyId(currencyId);
        if (currencyId !== defaultCurrencyId) {
            addExchangeRateField();
        } else {
            removeExchangeRateField();
        }
    };

    if(fields.length === 0) return <Loading absolute reverseColors />;

    return (
        <Container>
            <Form fields={fields} onSubmit={handleSubmit} onChange={onChange} isLoading={isLoading} error={error} submitText="Dodaj transakcję" submitClassName="bg-green-500" />
        </Container>
    );
};

export default AddTransaction;
