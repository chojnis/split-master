import { User, Transaction } from "~/api/types/entity";
import { Group } from "~/api/types/entity";

export type LoginResponse = {
    token: string;
    refresh_token: string;
    user: User;
}

export type RegisterResponse = User;

export type GroupResponse = Group;

export type GroupsResponse = Group[];

export type GroupMembersResponse = User[];

export type GroupTransactionResponse = Transaction[];

export type AddGroupResponse = Group;

export type UserResponse = User;