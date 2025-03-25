import { User, Transaction } from "~/api/entity";
import { Group } from "~/api/entity";

export type LoginResponse = {
    token: string;
    user: User;
}

export type RegisterResponse = User;

export type GroupsResponse = Group[];

export type GroupMembersResponse = User[];

export type GroupTransactionResponse = Transaction[];