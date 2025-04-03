import { createApi } from '@reduxjs/toolkit/query/react';
import { 
  LoginResponse, 
  RegisterResponse, 
  GroupsResponse, 
  GroupMembersResponse,
  GroupTransactionResponse,
  AddGroupResponse,
  GroupResponse,
  UserResponse,
  CurrenciesResponse,
  AddTransactionResponse,
  InvitesResponse,
  PairExchangeRateResponse,
  ExchangeRatesResponse
} from '~/api/types/response';
import { 
  LoginRequest, 
  RegisterRequest,
  AddGroupRequest,
  AddTransactionRequest,
  sendInviteRequest,
  PairExchangeRateRequest
} from '~/api/types/request';
import baseQuery from '~/api/query';

export const apiCall = createApi({
  reducerPath: 'api',
  baseQuery: baseQuery,
  refetchOnReconnect: true,
  endpoints: (builder) => ({
    login: builder.mutation<LoginResponse, LoginRequest>({
      query: (credentials) => ({
        url: 'login',
        method: 'POST',
        body: credentials,
      }),
    }),
    register: builder.mutation<RegisterResponse, RegisterRequest>({
      query: (credentials) => ({
        url: 'register',
        method: 'POST',
        body: credentials,
      }),
    }),
    getGroups: builder.query<GroupsResponse, number>({
      query: (page) => 'groups?page=' + page,
    }),
    getGroupMembers: builder.query<GroupMembersResponse, string>({
      query: (groupId) => ({
        url: `groups/${groupId}/members`,
        method: 'GET',
      }),
    }),
    getGroupTransactions: builder.query<GroupTransactionResponse, string>({
      query: (groupId) => ({
        url: `groups/${groupId}/transactions`,
        method: 'GET',
      }),
    }),
    addGroup: builder.mutation<AddGroupResponse, AddGroupRequest>({
      query: (data) => ({
        url: 'groups',
        method: 'POST',
        body: data,
      }),
    }),
    getGroup: builder.query<GroupResponse, string>({
      query: (groupId) => ({
        url: `groups/${groupId}`,
        method: 'GET',
      }),
      extraOptions: {
        refetchOnMountOrArgChange: true,
      },
    }),
    getUser: builder.query<UserResponse, string>({
      query: (userId) => ({
        url: `users/${userId}`,
        method: 'GET',
      }),
    }),
    getCurrencies: builder.query<CurrenciesResponse, void>({
      query: () => ({
        url: 'currencies',
        method: 'GET',
      }),
    }),
    addTransaction: builder.mutation<AddTransactionResponse, AddTransactionRequest>({
      query: ({groupId, data}) => ({
        url: `groups/${groupId}/transactions`,
        method: 'POST',
        body: data,
      }),
    }),
    getInvites: builder.query<InvitesResponse, void>({
      query: () => ({
        url: 'invites',
        method: 'GET',
      }),
    }),
    acceptInvite: builder.mutation<void, string>({
      query: (inviteId) => ({
        url: `invites/${inviteId}`,
        method: 'PATCH',
        body: JSON.stringify({ status: 'accepted' }),
        headers: {
          'Content-Type': 'application/merge-patch+json',
        }
      }),
    }),
    rejectInvite: builder.mutation<void, string>({
      query: (inviteId) => ({
        url: `invites/${inviteId}`,
        method: 'DELETE',
      }),
    }),
    sendInvite: builder.mutation<void, sendInviteRequest>({
      query: ({ groupId, data }) => ({
        url: `groups/${groupId}/members`,
        method: 'POST',
        body: data,
      }),
    }),
    leaveGroup: builder.mutation<void, string>({
      query: (groupId) => ({
        url: `groups/${groupId}/membership`,
        method: 'DELETE',
      })
    }),
    getPairExchangeRate: builder.query<PairExchangeRateResponse, PairExchangeRateRequest>({
      query: ({ from, to }) => ({
        url: `currency-exchange/${from}/${to}`,
        method: 'GET',
      }),
    }),
    getExchangeRates: builder.query<ExchangeRatesResponse, string>({
      query: ( to ) => ({
        url: `currency-exchange/${to}`,
        method: 'GET',
      }),
    }),
    updateGroup: builder.mutation<void, { groupId: string; data: AddGroupRequest }>({
      query: ({ groupId, data }) => ({
        url: `groups/${groupId}`,
        method: 'PATCH',
        body: data,
        headers: {
          'Content-Type': 'application/merge-patch+json',
        },
      }),
    }),
  }),
});

export const { 
  useLoginMutation, 
  useRegisterMutation,
  useGetGroupsQuery, 
  useLazyGetGroupsQuery,
  useGetGroupMembersQuery,
  useGetGroupTransactionsQuery,
  useAddGroupMutation,
  useGetGroupQuery,
  useGetUserQuery,
  useGetCurrenciesQuery,
  useAddTransactionMutation,
  useGetInvitesQuery,
  useAcceptInviteMutation,
  useRejectInviteMutation,
  useLeaveGroupMutation,
  useSendInviteMutation,
  useGetPairExchangeRateQuery,
  useGetExchangeRatesQuery,
  useUpdateGroupMutation,
} = apiCall;