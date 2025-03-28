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
}