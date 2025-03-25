export type User = {
    id: string;
    email: string;
    username?: string;
}

export type UserReference = {
    user: string;
}

export type Group = {
    id: string;
    groupName: string;
    description: string;
}

export type Currency = {
    id: string;
    code: string;
    name: string;
}

export type Transaction = {
    id: string;
    amount: number;
    currency: Currency;
    created_at: Date;
    payer: User;
    payees: User[];
}