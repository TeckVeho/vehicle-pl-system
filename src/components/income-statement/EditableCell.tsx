"use client";

import { useState, useRef, useEffect } from "react";
import { Input } from "@/components/ui/input";
import { formatCurrency } from "@/lib/utils";

interface EditableCellProps {
  value: number;
  onSave: (value: number) => Promise<void>;
  editMode?: boolean;
  disabled?: boolean;
  className?: string;
}

export function EditableCell({
  value,
  onSave,
  editMode = false,
  disabled = false,
  className = "",
}: EditableCellProps) {
  const [editing, setEditing] = useState(false);
  const [inputValue, setInputValue] = useState(String(value));
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (!editing) {
      setInputValue(String(value));
    }
  }, [value, editing]);

  useEffect(() => {
    if (editing && inputRef.current) {
      inputRef.current.focus();
      inputRef.current.select();
    }
  }, [editing]);

  const startEditing = () => {
    if (disabled) return;
    setEditing(true);
  };

  const handleBlur = async () => {
    setEditing(false);
    const num = parseFloat(inputValue.replace(/,/g, "")) || 0;
    if (num !== value) {
      setSaving(true);
      try {
        await onSave(num);
        setSaved(true);
        setTimeout(() => setSaved(false), 1500);
      } finally {
        setSaving(false);
      }
    }
  };

  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === "Enter") {
      (e.target as HTMLInputElement).blur();
    }
    if (e.key === "Escape") {
      setInputValue(String(value));
      setEditing(false);
    }
    if (e.key === "Tab") {
      (e.target as HTMLInputElement).blur();
    }
  };

  if (editing) {
    return (
      <Input
        ref={inputRef}
        type="text"
        inputMode="numeric"
        value={inputValue}
        onChange={(e) => setInputValue(e.target.value)}
        onBlur={handleBlur}
        onKeyDown={handleKeyDown}
        className={`h-8 w-full rounded-sm border-primary/40 text-right text-sm focus-visible:ring-1 focus-visible:ring-primary/50 ${className}`}
      />
    );
  }

  return (
    <div
      onClick={editMode ? startEditing : undefined}
      onDoubleClick={!editMode ? startEditing : undefined}
      title={editMode ? "クリックして編集" : "ダブルクリックして編集"}
      className={[
        "min-h-8 py-2 px-3 text-right transition-colors select-none",
        !disabled && editMode
          ? "cursor-pointer hover:bg-primary/5 hover:ring-1 hover:ring-inset hover:ring-primary/20"
          : !disabled
            ? "cursor-cell hover:bg-muted/30"
            : "cursor-default",
        saved ? "bg-emerald-50 text-emerald-700" : "",
        saving ? "opacity-60" : "",
        editMode ? "bg-amber-50/30" : "",
        className,
      ]
        .filter(Boolean)
        .join(" ")}
    >
      {formatCurrency(value)}
    </div>
  );
}
