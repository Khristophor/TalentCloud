import React from "react";

export interface SelectOption<T extends string | number> {
  value: T;
  label: string;
}

export interface SelectProps<T extends string | number> {
  htmlId: string;
  formName: string;
  label: string;
  selected: T | undefined;
  nullSelection: string | undefined;
  options: SelectOption<T>[];
  onChange: (event: React.ChangeEvent<HTMLSelectElement>) => void;
}

export default function Select<T extends string | number>(
  props: SelectProps<T>
): React.ReactElement<SelectProps<T>> {
  const {
    htmlId,
    formName,
    label,
    selected,
    nullSelection,
    options,
    onChange
  } = props;

  return (
    <div className="form__input-wrapper--select">
      <label className="form__label" htmlFor={htmlId}>
        {label}
      </label>
      <div className="form__select-wrapper fas fa-chevron-down">
        <select
          id={htmlId}
          className="form__input"
          name={formName}
          value={selected || ""}
          onChange={e => onChange(e)}
        >
          {nullSelection && (
            <option value="" disabled>
              {nullSelection}
            </option>
          )}
          {options.map(option => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      </div>
    </div>
  );
}
