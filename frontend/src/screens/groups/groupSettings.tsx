import { View, ScrollView } from 'react-native';

import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';
import { LogOut } from '~/lib/icons/LogOut'

import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';
import { 
    useLeaveGroupMutation, 
    useGetGroupQuery, 
    useSendInviteMutation, 
    useGetCurrenciesQuery, 
    useUpdateGroupMutation, 
    useLazyGetGroupMembershipsQuery,
    useKickFromGroupMutation,
    useChangeOwnershipMutation
} from '~/api';
import { Container } from '~/components/Container';
import Loading from '~/components/Loading';
import { Separator } from '~/components/Separator';

import { RootState } from '~/store';
import { useSelector } from 'react-redux';
import { showMessage, hideMessage } from "react-native-flash-message";
import CrownIcon from '~/lib/icons/Crown';
import TrashIcon from '~/lib/icons/Trash';


import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTrigger,
  } from '~/components/ui/dialog';

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '~/components/ui/table';
import Form, { FormDataType, FormFieldType } from '~/components/form/Form';
import { useEffect, useState } from 'react';
import { Currency } from '~/api/types/entity';
import { GroupMembershipsResponse } from '~/api/types/response';
import Error from '~/components/Error';

type GroupSettingsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupSettings'>;
type GroupSettingsScreenRouteProp = RouteProp<GroupsStackParamList, 'GroupSettings'>;

export default function GroupSettings() {

    const router = useRoute<GroupSettingsScreenRouteProp>();
    const groupId = router.params.groupId;
    const navigation = useNavigation<GroupSettingsStackNavigationProp>();

    const [leaveGroup, { isLoading: isLoadingLeave, error: errorLeave }] = useLeaveGroupMutation();
    const { 
        data: groupData, 
        isFetching: isFetchingGroup,
        isError: isErrorGroup,
        isSuccess: isSuccessGroup,
        refetch: refetchGroup
    } = useGetGroupQuery(groupId);
    const { 
        data: currencies, 
        isFetching: isFetchingCurrencies,
        isError: isErrorCurrencies,
        isSuccess: isSuccessCurrencies
    } = useGetCurrenciesQuery();
    const [sendInvite, { isLoading: isLoadingInvite, error: errorInvite }] = useSendInviteMutation();
    const [memberships, setMemberships] = useState<GroupMembershipsResponse>([]);

    const [
        triggerMemberships, 
        { isLoading: isLoadingMemberships }
    ] = useLazyGetGroupMembershipsQuery();

    const [kickFromGroup, { isLoading: isLoadingKick, error: errorKick }] = useKickFromGroupMutation();
    const [updateGroup, { isLoading: isLoadingUpdate, error: errorUpdate, isSuccess: isSuccessUpdateGroup }] = useUpdateGroupMutation();
    const [changeOwnership] = useChangeOwnershipMutation();

    const userId = useSelector((state: RootState) => state.auth.user?.id);
    const [isOwner, setIsOwner] = useState<boolean>(groupData?.owner.id === userId);

    const [addMemberFields, setAddMemberFields] = useState<FormFieldType[]>([
        { label: 'E-mail użytkownika', placeholder: 'user@example.com', name: 'email', type: 'text', required: true }
    ]);

    const [editGroupFields, setEditGroupFields] = useState<FormFieldType[]>([]);

    const [openGroupDialog, setOpenGroupDialog] = useState<boolean>(false);
    const [openMemberDialog, setOpenMemberDialog] = useState<boolean>(false);

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
            showMessage({
                message: "Nie udało się pobrać danych grupy. Spróbuj ponownie.",
                type: "danger"
            })
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
                // required: true,
                disabled: true,
                selectOptions: currencies.map((currency: Currency) => ({ label: currency.name, value: currency.id })),
                value: groupData.currency.id,
                description: 'Nie można zmienić waluty po dodaniu grupy.',
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

    useEffect(() => {
        if (groupData) {
            setIsOwner(groupData.owner.id === userId);
        }
    }, [groupData, userId]);
    
    const handleLeaveGroup = async () => {
        try {
            const {error: errorLeave} = await leaveGroup(groupId);

            if (errorLeave) {
                showMessage({
                    message: "Nie udało się opuścić grupy. Spróbuj ponownie.",
                    type: "danger"
                })
                return;
            }

            showMessage({
                message: "Opuściłeś grupę.",
                type: "success",
            });
            navigation.reset({
                index: 0,
                routes: [{ name: "GroupsList" }],
            });
        } catch (error) {
            showMessage({
                message: "Nie udało się opuścić grupy. Spróbuj ponownie.",
                type: "danger"
            });
        }
    };

    const handleInviteSubmit = async (formData: FormDataType) => {
        try {
            const { email } = formData as { email: string };

            await sendInvite({ groupId, data: { email } });
            if (errorInvite) {
                showMessage({
                    message: "Nie udało się wysłać zaproszenia. Spróbuj ponownie.",
                    type: "danger"
                })
                return;
            }

            showMessage({
                message: "Zaproszenie zostało wysłane.",
                type: "success",
            });
            // setOpenMemberDialog(false);
            getMemberships();

            setAddMemberFields((prev) => {
                const emailField = prev.find((item) => item.name === "email");
                if (emailField) {
                    emailField.value = "";
                }
                return [...prev];
            });
            
        } catch (error) {
            showMessage({
                message: "Nie udało się wysłać zaproszenia. Spróbuj ponownie.",
                type: "danger"
            })
        }
    };

    const handleSaveGroupData = async (formData: FormDataType) => {
        try {
            const { groupName, description, currency } = formData as { groupName: string; description: string; currency: number };

            const { error } = await updateGroup({ groupId, data: { groupName, description, currency } });
            if (error) {
                showMessage({
                    message: "Nie udało się zaktualizować danych grupy. Spróbuj ponownie.",
                    type: "danger"
                })
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
            setOpenGroupDialog(false);
        } catch (error) {
            showMessage({
                message: "Nie udało się zaktualizować danych grupy. Spróbuj ponownie.",
                type: "danger"
            })
        }
    };

    const handleOpenChangeMemberDialog = async (open: boolean) => {
        setOpenMemberDialog(open);
        getMemberships();
    };

    const getMemberships = async () => {
        const { data, error } = await triggerMemberships(groupId);

        if (error) {
            showMessage({
                message: "Nie udało się pobrać członków grupy. Spróbuj ponownie.",
                type: "danger"
            })
            return;
        }

        if (data) {
            setMemberships(data);
        }
    }

    const handleKickFromGroup = async (userId: string) => {
        try {
            const { error } = await kickFromGroup({groupId, userId});
            if (error) {
                showMessage({
                    message: "Nie udało się usunąć użytkownika z grupy. Spróbuj ponownie.",
                    type: "danger"
                })
                return;
            }
            showMessage({
                message: "Użytkownik został usunięty z grupy.",
                type: "success",
            });
            getMemberships();
        } catch (error) {
            showMessage({
                message: "Nie udało się usunąć użytkownika z grupy. Spróbuj ponownie.",
                type: "danger"
            })
        }
    };

    const handleChangeOwnership = async (userId: string) => {
        try {
            const { error } = await changeOwnership({groupId, data: { owner: userId }});
            if (error) {
                showMessage({
                    message: "Nie udało się zmienić właściciela grupy. Spróbuj ponownie.",
                    type: "danger"
                })
                return;
            }
            showMessage({
                message: "Zmieniono właściciela grupy.",
                type: "success",
            });
            setOpenMemberDialog(false);
            setIsOwner(false);
        } catch (error) {
            showMessage({
                message: "Nie udało się zmienić właściciela grupy. Spróbuj ponownie.",
                type: "danger"
            })
        }
    }

    if(!isSuccessGroup) {
        return (
            <Container>
                <Loading />
            </Container>
        );
    }

    return (
        <Container>
            {isOwner && (
                <Dialog className="mb-2" open={openGroupDialog} onOpenChange={setOpenGroupDialog}>
                    <DialogTrigger asChild>
                        <Button variant='outline' className="dark:bg-[#101828] dark:border-transparent">
                            <Text>Edytuj dane grupy</Text>
                        </Button>
                    </DialogTrigger>
                    <DialogContent className='sm:max-w-[425px] dark:bg-[#1e2939]'>
                        <DialogFooter>
                            <Form 
                                fields={editGroupFields} 
                                onSubmit={handleSaveGroupData} 
                                isLoading={isLoadingUpdate} 
                                error={errorUpdate} 
                                submitClassName="bg-green-500" 
                                submitText="Zapisz" 
                                submitTextClassName="text-white" 
                            />
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            )}

            {isOwner && (
                <Dialog open={openMemberDialog} onOpenChange={handleOpenChangeMemberDialog}>
                    <DialogTrigger asChild>
                        <Button variant='outline' className="dark:bg-[#101828] dark:border-transparent">
                            <Text>Zarządzaj członkami</Text>
                        </Button>
                    </DialogTrigger>
                    <DialogContent className='sm:max-w-[425px] flex flex-col justify-between dark:bg-[#1e2939]'>
                        <DialogHeader className="relative">
                            {isLoadingKick && (
                                <Loading absolute reverseColors />
                            )}
                            <Table>
                                <TableHeader>
                                    <TableRow className="w-full flex flex-row items-center justify-between">
                                    <TableHead className="px-0.5">
                                        <Text>Członkowie</Text>
                                    </TableHead>
                                    </TableRow>
                                </TableHeader>

                                <ScrollView className="h-60" showsVerticalScrollIndicator={true}>
                                    {isLoadingMemberships ? (
                                        <TableBody className="flex flex-col w-full h-60">
                                            <TableRow className="flex flex-row items-center justify-center">
                                                <TableCell className="flex items-center justify-center">
                                                    <Loading />
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    ) : (
                                        <TableBody className="flex flex-col w-full">
                                            {memberships.map((item) => (
                                                <TableRow key={item.user.id} className="flex flex-row items-center justify-between">
                                                    <TableCell className="flex items-center flex-row">
                                                        <Text>{item.user.email}</Text>
                                                        {item.user.id === groupData.owner.id && (
                                                            <CrownIcon className="text-yellow-500 ml-2" width={16} height={16} />
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="flex items-center justify-center">
                                                        {item.status === "pending" ? (
                                                            <Text>Wysłano zaproszenie</Text>
                                                        ) 
                                                        : item.user.id === groupData.owner.id ? (
                                                            <Button variant="destructive" className="" onPress={() => {}} disabled={true}>
                                                                <TrashIcon className="text-white" width={16} height={16} />
                                                            </Button>
                                                        ) : (
                                                            <View className="flex flex-row items-center gap-2">
                                                                <Button variant="outline" className="bg-transparent dark:border-white" onPress={() => {handleChangeOwnership(item.user.id)}}>
                                                                    <CrownIcon className="text-yellow-500" width={16} height={16} />
                                                                </Button>
                                                                <Button variant="destructive" className="" onPress={() => {handleKickFromGroup(item.user.id)}}>
                                                                    <TrashIcon className="text-white" width={16} height={16} />
                                                                </Button>
                                                            </View>
                                                        )}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    )}
                                </ScrollView>
                                {errorKick && (
                                    <Error message={'detail' in errorKick ? errorKick.detail : ""} />
                                )}
                            </Table>
                        </DialogHeader>
                        <DialogFooter className="flex flex-col">
                            <Table className="h-80">
                                <TableHeader>
                                    <TableRow className="w-full flex flex-row items-center justify-between">
                                        <TableHead className="px-0.5">
                                            <Text>Zaproś</Text>
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow>
                                        <TableCell>
                                        <Form fields={addMemberFields} onSubmit={handleInviteSubmit} isLoading={isLoadingInvite} error={errorInvite} submitText="Wyślij zaproszenie" />
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
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
            {errorLeave && (
                <Error className="mt-2" message={'detail' in errorLeave ? errorLeave.detail : ""} />
            )}
        </Container>
    );
}