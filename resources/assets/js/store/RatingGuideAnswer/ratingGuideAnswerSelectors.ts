import { RootState } from "../store";
import { RatingGuideAnswerState } from "./ratingGuideAnswerReducer";
import { RatingGuideAnswer } from "../../models/types";
import { getRatingGuideQuestionsByJob } from "../RatingGuideQuestion/ratingGuideQuestionSelectors";
import { getId, hasKey } from "../../helpers/queries";

const stateSlice = (state: RootState): RatingGuideAnswerState =>
  state.ratingGuideAnswer;

export const getRatingGuideAnswers = (state: RootState): RatingGuideAnswer[] =>
  Object.values(stateSlice(state).ratingGuideAnswers);

export const getRatingGuideAnswersByJob = (
  state: RootState,
  jobId: number,
): RatingGuideAnswer[] => {
  const questionIds = getRatingGuideQuestionsByJob(state, jobId).map(getId);
  return getRatingGuideAnswers(state).filter(
    (answer): boolean => questionIds.includes(answer.rating_guide_question_id),
  );
};

export const getRatingGuideAnswerById = (
  state: RootState,
  id: number,
): RatingGuideAnswer | null =>
  hasKey(stateSlice(state).ratingGuideAnswers, id)
    ? stateSlice(state).ratingGuideAnswers[id]
    : null;
