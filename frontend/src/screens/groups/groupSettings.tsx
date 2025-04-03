import { Alert, View } from 'react-native';

import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';
import { LogOut } from '~/lib/icons/LogOut'

import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';
import { useLeaveGroupMutation, useGetGroupQuery, useSendInviteMutation, useGetCurrenciesQuery, useUpdateGroupMutation } from '~/api';
import { Container } from '~/components/Container';
import Loading from '~/components/Loading';
import { Separator } from '~/components/Separator';

import { RootState } from '~/store';
import { useSelector } from 'react-redux';
import { Toast } from 'toastify-react-native'
import { showMessage, hideMessage } from "react-native-flash-message";


import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
  } from '~/components/ui/dialog';
import Form, { FormDataType, FormFieldType } from '~/components/form/Form';
import { useEffect, useState } from 'react';
import { Currency } from '~/api/types/entity';

type GroupSettingsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupSettings'>;
type GroupSettingsScreenRouteProp = RouteProp<GroupsStackParamList, 'GroupSettings'>;

export default function GroupSettings() {

    const router = useRoute<GroupSettingsScreenRouteProp>();
    const groupId = router.params.groupId;
    const navigation = useNavigation<GroupSettingsStackNavigationProp>();

    const [leaveGroup, { isLoading: isLoadingLeave, error: errorLeave }] = useLeaveGroupMutation();
    const { 
        data: groupData, 
        isLoading: isLoadingGroup, 
        isFetching: isFetchingGroup,
        isError: isErrorGroup,
        isSuccess: isSuccessGroup,
        error: errorGroup,
        refetch: refetchGroup
    } = useGetGroupQuery(groupId);
    const { 
        data: currencies, 
        isLoading: isLoadingCurrencies, 
        isFetching: isFetchingCurrencies,
        isError: isErrorCurrencies,
        isSuccess: isSuccessCurrencies,
        error: errorCurrencies
    } = useGetCurrenciesQuery();
    const [sendInvite, { isLoading: isLoadingInvite, error: errorInvite }] = useSendInviteMutation();

    const [updateGroup, { isLoading: isLoadingUpdate, error: errorUpdate, isSuccess: isSuccessUpdateGroup }] = useUpdateGroupMutation();

    const userId = useSelector((state: RootState) => state.auth.user.id);
    const isOwner = groupData?.owner.id === userId;

    const [addMemberFields, setAddMemberFields] = useState<FormFieldType[]>([
        { label: 'E-mail użytkownika', placeholder: 'user@example.com', name: 'email', type: 'text', required: true }
    ]);

    const [editGroupFields, setEditGroupFields] = useState<FormFieldType[]>([]);

    const [openDialog, setOpenDialog] = useState<boolean>(false);

    useEffect(() => {
        refetchGroup();
    }, [isSuccessUpdateGroup]);

    useEffect(() => {
        if (
            isFetchingGroup
            || isFetchingCurrencies
        ) return;

        if (
            isErrorGroup 
            || isErrorCurrencies
            || !isSuccessGroup
            || !isSuccessCurrencies
        ) {
            Alert.alert('Błąd', 'Nie można pobrać danych grupy. Spróbuj ponownie.');
            navigation.goBack();
            return;
        }

        setEditGroupFields([
            { 
                label: 'Nazwa grupy', 
                placeholder: 'Pączki', 
                name: 'groupName', 
                type: 'text', 
                required: true,
                value: groupData.groupName,
            },
            { 
                label: 'Opis grupy', 
                placeholder: 'Grupa dla miłośników pączków', 
                name: 'description', 
                type: 'text',
                value: groupData.description,
            },
            {
                label: 'Waluta',
                name: 'currency',
                type: 'select',
                required: true,
                selectOptions: currencies.map((currency: Currency) => ({ label: currency.name, value: currency.id })),
                value: groupData.currency.id,
            },
        ]);

    }, [
        groupData, 
        currencies,
        isFetchingGroup,
        isFetchingCurrencies,
        isErrorGroup,
        isErrorCurrencies,
        isSuccessGroup,
        isSuccessCurrencies
    ]);
    
    const handleLeaveGroup = async () => {
        try {
            await leaveGroup(groupId);

            if (errorLeave) {
                Alert.alert("Błąd", "Nie udało się opuścić grupy. Spróbuj ponownie.");
                return;
            }

            navigation.navigate("GroupsList");
        } catch (error) {
            console.error("Error leaving group:", error);
        }
    };

    const handleInviteSubmit = async (formData: FormDataType) => {
        try {
            const { email } = formData as { email: string };

            await sendInvite({ groupId, data: { email } });
            if (errorInvite) {
                Alert.alert("Błąd", "Nie udało się wysłać zaproszenia. Sprawdź adres e-mail i spróbuj ponownie.");
                return;
            }
            Alert.alert("Sukces", "Zaproszenie zostało wysłane.");
        } catch (error) {
            console.error("Error sending invite:", error);
            Alert.alert("Błąd", "Nie udało się wysłać zaproszenia. Spróbuj ponownie.");
        }
    };

    const handleSaveGroupData = async (formData: FormDataType) => {
        try {
            const { groupName, description, currency } = formData as { groupName: string; description: string; currency: number };

            const { error } = await updateGroup({ groupId, data: { groupName, description, currency } });
            if (error) {
                Alert.alert("Błąd", "Nie udało się zaktualizować danych grupy. Spróbuj ponownie.");
                return;
            }

            showMessage({
                message: "Dane grupy zostały zaktualizowane.",
                type: "success",
                duration: 1000,
                // floating: true,
                animated: true,
                style: {opacity: .95},

            });
            setOpenDialog(false);
        } catch (error) {
            console.error("Error updating group data:", error);
            Alert.alert("Błąd", "Nie udało się zaktualizować danych grupy. Spróbuj ponownie.");
        }
    };


    return (
        <Container>
            {isOwner && (
                <Dialog className="mb-2" open={openDialog} onOpenChange={setOpenDialog}>
                    <DialogTrigger asChild>
                        <Button variant='outline'>
                            <Text>Edytuj dane grupy</Text>
                        </Button>
                    </DialogTrigger>
                    <DialogContent className='sm:max-w-[425px]'>
                        <DialogHeader>
                            <Form 
                                fields={editGroupFields} 
                                onSubmit={handleSaveGroupData} 
                                isLoading={isLoadingUpdate} 
                                error={errorUpdate} 
                                submitClassName="bg-green-500" 
                                submitText="Zapisz" 
                                submitTextClassName="text-white" 
                            />
                        </DialogHeader>
                    </DialogContent>
                </Dialog>
            )}

            {isOwner && (
                <Dialog>
                    <DialogTrigger asChild>
                        <Button variant='outline'>
                            <Text>Dodaj członka</Text>
                        </Button>
                    </DialogTrigger>
                    <DialogContent className='sm:max-w-[425px]'>
                        <DialogHeader>
                            <Form fields={addMemberFields} onSubmit={handleInviteSubmit} isLoading={isLoadingInvite} error={errorInvite} submitText="Wyślij zaproszenie" />
                        </DialogHeader>
                        <DialogFooter>
                            <DialogClose asChild>
                            <Button>
                                <Text>OK</Text>
                            </Button>
                            </DialogClose>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            )}

            <Separator />
            <Button variant={"destructive"} onPress={handleLeaveGroup} disabled={isLoadingLeave}>
                {isLoadingLeave ? (
                    <Loading />
                ) : (
                    <View className="flex flex-row items-center gap-2">
                        <LogOut className="text-white" width={24} height={24} />
                        <Text>Opuść grupę</Text>
                    </View>
                )}
            </Button>
        </Container>
    );
}