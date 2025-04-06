import { Alert } from 'react-native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RouteProp, useFocusEffect, useNavigation, useRoute } from '@react-navigation/native';
import { Container } from '~/components/Container';
import Form, { FormDataType, FormFieldType } from '~/components/form/Form';
import { 
    useEditTransactionMutation, 
    useGetCurrenciesQuery, 
    useGetGroupMembersQuery, 
    useGetPairExchangeRateQuery,
    useGetTransactionQuery
} from '~/api';
import { GroupsStackParamList } from '~/navigation/groups';
import { Currency } from '~/api/types/entity';
import Loading from '~/components/Loading';
import { useCallback, useEffect, useState } from 'react';
import { showMessage } from 'react-native-flash-message';
import { AddTransactionRequest } from '~/api/types/request';

type EditTransactionScreenNavigationProps = StackNavigationProp<GroupsStackParamList, 'EditTransaction'>;
type EditTransactionScreenRouteProps = RouteProp<GroupsStackParamList, 'EditTransaction'>;

const EditTransaction = () => {
    const navigation = useNavigation<EditTransactionScreenNavigationProps>();
    const route = useRoute<EditTransactionScreenRouteProps>();
    const { groupId, transactionId, defaultCurrencyId } = route.params;

    const [updateTransaction, { isLoading, error }] = useEditTransactionMutation();
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

    const { 
        data: transactionData, 
        isError: isErrorTransaction, 
        isLoading: isLoadingTransaction,
        isFetching: isFetchingTransaction,
        isSuccess: isSuccessTransaction,
        refetch: refetchTransaction
    } = useGetTransactionQuery(transactionId);

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

    const isInitFetching = isFetchingCurrencies || isFetchingMembers || isFetchingTransaction;
    const isLoadError = isErrorCurrencies || isErrorMembers || isErrorTransaction;
    const isLoadDataReady = isSuccessCurrencies && isSuccessMembers && isSuccessTransaction;

    useFocusEffect(
        useCallback(() => {
            refetchCurrencies();
            refetchMembers();
            refetchTransaction();
        }, [])
    );

    useEffect(() => {
        if (isInitFetching) return;

        if (
            isLoadError
            || !isLoadDataReady
            || currencies.length === 0
            || members.length === 0
        ) {
            showMessage({
                message: 'Nie można pobrać walut lub członków grupy. Spróbuj ponownie.',
                type: 'danger',
            });
            navigation.goBack();
            return;
        }
        
        if(fields.length === 0) {

            setFields([
                { 
                    label: 'Nazwa transakcji', 
                    placeholder: 'Pączki', 
                    name: 'name', 
                    type: 'text', 
                    required: true,
                    value: transactionData.name, 
                },
                { 
                    label: 'Wartość transakcji', 
                    placeholder: '0.00', 
                    name: 'amount', 
                    type: 'number', 
                    width: 70, 
                    required: true,
                    value: transactionData.amount.toString(),
                },
                {
                    label: 'Waluta',
                    name: 'currencyId',
                    type: 'select',
                    width: 30,
                    required: true,
                    selectOptions: currencies.map((currency: Currency) => ({ label: currency.name, value: currency.id })),
                    value: transactionData.currency.id,
                },
                {
                    label: 'Płatnik',
                    name: 'payerId',
                    type: 'select',
                    required: true,
                    selectOptions: members.map((member) => ({ label: member.username || member.email, value: member.id })),
                    value: transactionData.payer.id,
                },
                {
                    label: 'Odbiorcy',
                    name: 'payeesIds',
                    type: 'select',
                    required: true,
                    multiple: true,
                    selectOptions: members.map((member) => ({ label: member.username || member.email, value: member.id })),
                    value: transactionData.payees.map((payee) => payee.id),
                },
            ]);

            setSelectedCurrencyId(transactionData.currency.id);
        } else {

            setFields((prevFields) => 
                prevFields.map((field) => 
                    field.name === 'currencyId' 
                    ? { ...field, selectOptions: currencies.map((currency: Currency) => ({ label: currency.name, value: currency.id })) }
                    : field
                )
            );
            setFields((prevFields) =>
                prevFields.map((field) => 
                    field.name === 'payerId' 
                    ? { ...field, selectOptions: members.map((member) => ({ label: member.username || member.email, value: member.id })) }
                    : field
                )
            );
            setFields((prevFields) =>
                prevFields.map((field) => 
                    field.name === 'payeesIds' 
                    ? { ...field, selectOptions: members.map((member) => ({ label: member.username || member.email, value: member.id })) }
                    : field
                )
            );


        }




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

        let value = '';
        if (
            !isFetchingTransaction
            && !isErrorTransaction
            && isSuccessTransaction
        ) {
            value = transactionData.exchangeRate.toString();
        }
    
        setFields((prevFields) => 
            prevFields.map((field) => 
                field.name === 'exchangeRate' 
                ? { ...field, placeholder: placeholder, value: value }
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
        const formDataWithNumberAmount = {
            ...formData,
            amount: parseFloat(formData.amount as string)
        };
        const requestData = formDataWithNumberAmount as AddTransactionRequest;
        try {
            const { data, error: errorUpdate } = await updateTransaction({ transactionId, data: requestData });
            if(errorUpdate) {
                showMessage({
                    message: 'Nie udało się zaktualizować transakcji. Spróbuj ponownie.',
                    type: 'danger'
                });
                return;
            }
            
            navigation.goBack();
            showMessage({
                message: 'Transakcja została zaktualizowana.',
                type: 'success',
                duration: 1000,
            })
        } catch (err) {
            showMessage({
                message: 'Nie udało się zaktualizować transakcji. Spróbuj ponownie.',
                type: 'danger'
            });
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
            <Form fields={fields} onSubmit={handleSubmit} onChange={onChange} isLoading={isLoading} error={error} submitText="Zapisz transakcję" submitClassName="bg-green-500" />
        </Container>
    );
};

export default EditTransaction;
