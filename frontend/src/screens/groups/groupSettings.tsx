import { Alert, View } from 'react-native';

import { Button } from '~/components/ui/button';
import { Text } from '~/components/ui/text';
import { LogOut } from '~/lib/icons/LogOut'

import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { GroupsStackParamList } from '~/navigation/groups';
import { StackNavigationProp } from '@react-navigation/stack';
import { useLeaveGroupMutation, useGetGroupQuery, useSendInviteMutation } from '~/api';
import { Container } from '~/components/Container';
import Loading from '~/components/Loading';
import { Separator } from '~/components/Separator';

import { RootState } from '~/store';
import { useSelector } from 'react-redux';

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

type GroupSettingsStackNavigationProp = StackNavigationProp<GroupsStackParamList, 'GroupSettings'>;
type GroupSettingsScreenRouteProp = RouteProp<GroupsStackParamList, 'GroupSettings'>;

export default function GroupSettings() {

    const router = useRoute<GroupSettingsScreenRouteProp>();
    const groupId = router.params.groupId;
    const navigation = useNavigation<GroupSettingsStackNavigationProp>();

    const [leaveGroup, { isLoading: isLoadingLeave, error: errorLeave }] = useLeaveGroupMutation();
    const { data: groupData, isLoading: isLoadingGroup, error: errorGroup } = useGetGroupQuery(groupId);
    const [sendInvite, { isLoading: isLoadingInvite, error: errorInvite }] = useSendInviteMutation();

    const userId = useSelector((state: RootState) => state.auth.user.id);
    const isOwner = groupData?.owner.id === userId;

    const fields = [
        { label: 'E-mail użytkownika', placeholder: 'user@example.com', name: 'email', type: 'text', required: true } as FormFieldType,
    ];
    
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

    return (
        <Container>
            {isOwner && (
                <Dialog>
                    <DialogTrigger asChild>
                        <Button variant='outline'>
                            <Text>Dodaj członka</Text>
                        </Button>
                    </DialogTrigger>
                    <DialogContent className='sm:max-w-[425px]'>
                        <DialogHeader>
                            <Form fields={fields} onSubmit={handleInviteSubmit} isLoading={isLoadingInvite} error={errorInvite} submitText="Wyślij zaproszenie" />
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