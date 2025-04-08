import { User, Transaction, Currency, ExchangeRate } from "~/api/types/entity";
import { Group, Invite, MembershipStatus, GroupSettlement } from "~/api/types/entity";

export type LoginResponse = {
    token: string;
    refresh_token: string;
    user: User;
}

export type RegisterResponse = User;

export type GroupResponse = Group;

export type GroupsResponse = Group[];

export type GroupMembersResponse = User[];

export type GroupMembershipsResponse = {
    user: User;
    status: MembershipStatus;
}[];

export type GroupTransactionResponse = Transaction[];

export type AddGroupResponse = Group;

export type UserResponse = User;

export type CurrenciesResponse = Currency[];

export type AddTransactionResponse = Transaction;

export type InvitesResponse = Invite[];

export type PairExchangeRateResponse = ExchangeRate;

export type ExchangeRatesResponse = ExchangeRate[];

export type GroupSettlementsResponse = GroupSettlement[];


