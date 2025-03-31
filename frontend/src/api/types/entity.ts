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
}

export type Invite = {
    id: string;
    createdAt: Date;
    group: {
        groupName: string;
        description: string;
    }
}