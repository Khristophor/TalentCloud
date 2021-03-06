import { Action as ReduxAction, ActionCreator } from "redux";

export interface Action<T extends string, P, M = {}> {
  type: T;
  payload: P;
  error?: boolean;
  meta?: M;
}

export interface ErrorAction<T extends string, M = {}>
  extends Action<T, Error, M> {
  error: true;
}

const createAction: ActionCreator<ReduxAction> = <T extends string, P>(
  type: T,
  payload: P,
  error = false,
): Action<T, P> => {
  return {
    type,
    payload,
    error,
  };
};

export default createAction;
