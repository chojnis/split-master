import { Transaction } from "./entity";

export type LoginRequest = {
    email: string;
    password: string;
}

export type RegisterRequest = {
    email: string;
    plainPassword: string;
}

export type RefreshTokenRequest = {
    refresh_token: string;
}

export type AddGroupRequest = {
    groupName: string;
    description: string;
    currency: number;
}

export type AddTransactionRequest = {
    name: string;
    amount: number;
    currencyId: string;
    payerId: string;
    payeesIds: string[];
    exchangeRate: number | undefined;
    transactionDate: Date | undefined;
}

export type sendInviteRequest = {
    groupId: string;
    data: {
        email: string;
    }
}

export type PairExchangeRateRequest = {
    from: string;
    to: string;
}
