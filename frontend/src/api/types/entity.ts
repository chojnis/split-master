export type User = {
    id: string;
    email: string;
    username?: string;
}
export type Group = {
    id: string;
    groupName: string;
    description: string;
    owner: User;
    currency: Currency;
}

export type Currency = {
    id: string;
    code: string;
    name: string;
}

export type Transaction = {
    id: string;
    name: string;
    amount: number;
    currency: Currency;
    created_at: Date;
    payer: User;
    payees: User[];
    exchangeRate?: number;
    transactionDate: Date;
}

export type Invite = {
    id: string;
    createdAt: Date;
    group: {
        groupName: string;
        description: string;
    }
}

export type ExchangeRate = {
    fromCurrency: string;
    toCurrency: string;
    rate: number;
    date: Date;
}

export type MembershipStatus = "accepted" | "pending";