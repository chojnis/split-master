import { User, UserReference } from "~/api/entity";
import { Group } from "~/api/entity";

export type LoginResponse = {
    token: string;
    user: User;
}

export type RegisterResponse = User;

export type GroupsResponse = Group[];

export type GroupMembersResponse = {
    user: User;
}[];

export type GroupTransactionResponse = {
    id: string;
    amount: number;
    description: string;
    date: string;
    user: UserReference;
}[];