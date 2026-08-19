/**
 * Campo de entrada reutilizável.
 */

interface InputProps {
  label: string;
  type?: string;
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  required?: boolean;
}

export function Input({
  label,
  type = 'text',
  value,
  onChange,
  placeholder,
  required = false,
}: InputProps) {
  return (
    <div className="input-group">
      <label className="input-label">{label}</label>
      <input
        className="input-field"
        type={type}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder={placeholder}
        required={required}
      />
    </div>
  );
}
