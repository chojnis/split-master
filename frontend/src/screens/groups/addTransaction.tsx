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
import { AddTransactionRequest } from '~/api/types/request';
import { showMessage } from 'react-native-flash-message';

type AddTransactionScreenNavigationProps = StackNavigationProp<GroupsStackParamList, 'AddTransaction'>;
type AddTransactionScreenRouteProps = RouteProp<GroupsStackParamList, 'AddTransaction'>;

/**
 * Screen component for creating a new transaction within a group.
 * 
 * This component allows users to:
 * - Create a transaction by filling out a form
 * - Specify transaction details like name, amount, currency, and date
 * - Select who paid and who the recipients are
 * - Handle currency conversion with automatic or manual exchange rates
 * 
 */
const AddTransaction = () => {
    const navigation = useNavigation<AddTransactionScreenNavigationProps>();
    const route = useRoute<AddTransactionScreenRouteProps>();
    const { groupId, defaultCurrency } = route.params;

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
    const [selectedDate, setSelectedDate] = useState<Date | undefined>(undefined);

    const { 
        data: exchangeRateData, 
        isLoading: isLoadingExchangeRate, 
        isError: isErrorExchangeRate,
        isSuccess: isSuccessExchangeRate,
        isFetching: isFetchingExchangeRate,
        refetch: refetchExchangeRate 
    } = useGetPairExchangeRateQuery(
        {from: selectedCurrencyId, to: defaultCurrency.id, date: selectedDate?.toISOString().split('T')[0]}, 
        {skip: !selectedCurrencyId || !defaultCurrency.id || selectedCurrencyId === defaultCurrency.id || selectedCurrencyId === "" || !selectedDate}
    );

    const [fields, setFields] = useState<FormFieldType[]>([]);

    const isLoadError = isErrorCurrencies || isErrorMembers;
    const isLoadDataReady = isSuccessCurrencies && isSuccessMembers;

    useFocusEffect(
        useCallback(() => {
            refetchCurrencies();
            refetchMembers();
        }, [])
    );

    useEffect(() => {
        if (isFetchingCurrencies || isFetchingMembers) return;

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
                { label: 'Nazwa', placeholder: 'Pączki', name: 'name', type: 'text', required: true },
                { label: 'Kwota', placeholder: '0.00', name: 'amount', type: 'number', width: 70, required: true },
                {
                    label: 'Waluta',
                    name: 'currencyId',
                    type: 'select',
                    width: 30,
                    required: true,
                    selectOptions: currencies.map((currency: Currency) => ({ label: currency.name, value: currency.id })),
                    value: currencies[0].id,
                },
                {
                    label: 'Kurs wymiany',
                    name: 'exchangeRate',
                    type: 'number',
                    hidden: defaultCurrency.id === currencies[0].id,
                    value: '',
                    description: 'Pozostaw puste, aby użyć automatycznego kursu.',
                },
                {
                    label: 'Kto zapłacił?',
                    name: 'payerId',
                    type: 'select',
                    required: true,
                    width: 60,
                    selectOptions: members.map((member) => ({ label: member.username || member.email, value: member.id })),
                    value: members[0].id,
                },
                {
                    label: 'Kiedy?',
                    name: 'transactionDate',
                    type: 'date',
                    required: true,
                    width: 40,
                    value: new Date(),
                },
                {
                    label: 'Odbiorcy',
                    name: 'payeesIds',
                    type: 'select',
                    required: true,
                    multiple: true,
                    selectOptions: members.map((member) => ({ label: member.username || member.email, value: member.id })),
                    value: [members[0].id],
                },
            ]);

            setSelectedCurrencyId(currencies[0].id);
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
        let placeholder = '...';

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
        const formDataWithNumberAmount = {
            ...formData,
            amount: parseFloat(formData.amount as string),
            transactionDate: (formData.transactionDate as Date).toISOString().split('T')[0],
            exchangeRate: formData.exchangeRate ? parseFloat(formData.exchangeRate as string) : undefined,
        };
        const requestData = formDataWithNumberAmount as AddTransactionRequest;
        try {
            const { data } = await fetchAddTransaction({ groupId, data: requestData });
            if (data) {
                showMessage({
                    message: 'Transakcja została dodana pomyślnie.',
                    type: 'success',
                });
                navigation.goBack();
            }
        } catch (err) {
            showMessage({
                message: 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie.',
                type: 'danger',
            });
        }
    };

    const addExchangeRateField = () => {
        setFields((prevFields) => 
            prevFields.map((field) => 
                field.name === 'exchangeRate' 
                ? { ...field, hidden: false }
                : field
            )
        );
    };

    const removeExchangeRateField = () => {
        setFields((prevFields) => 
            prevFields.map((field) => 
                field.name === 'exchangeRate' 
                ? { ...field, hidden: true }
                : field
            )
        );    };

    const onChange = (data: FormDataType) => {
        const { currencyId, transactionDate } = data as { currencyId: string, transactionDate: Date };
        setSelectedCurrencyId(currencyId);
        setSelectedDate(transactionDate);
        if (currencyId !== defaultCurrency.id) {
            addExchangeRateField();
        } else {
            removeExchangeRateField();
        }
    };

    if(fields.length === 0) return <Loading absolute reverseColors />;

    return (
        <Container>
            <Form 
                fields={fields} 
                onSubmit={handleSubmit} 
                onChange={onChange} 
                isLoading={isLoading} 
                error={error} 
                submitText="Dodaj transakcję" 
                submitClassName="bg-green-500" 
                submitTextClassName='text-white'
            />
        </Container>
    );
};

export default AddTransaction;
