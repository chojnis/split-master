import { User } from "~/api/entity";
import { Group } from "~/api/entity";

export type LoginResponse = {
    token: string;
    user: User;
}

export type RegisterResponse = User;

export type GroupsResponse = {
    "@context": string;
    "@id": string;
    "@type": string;
    totalItems: number;
    member: Group[];
}