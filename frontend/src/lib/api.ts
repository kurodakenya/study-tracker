const API_BASE = "/api";

function getToken(): string | null {
  if (typeof window === "undefined") return null;
  return localStorage.getItem("token");
}

export function setToken(token: string) {
  localStorage.setItem("token", token);
}

export function clearToken() {
  localStorage.removeItem("token");
}

async function request<T>(
  path: string,
  options: RequestInit = {}
): Promise<T> {
  const token = getToken();
  const headers: Record<string, string> = {
    "Content-Type": "application/json",
    Accept: "application/json",
    ...((options.headers as Record<string, string>) || {}),
  };
  if (token) {
    headers["Authorization"] = `Bearer ${token}`;
  }

  const res = await fetch(`${API_BASE}${path}`, {
    ...options,
    headers,
  });

  if (res.status === 204) return null as T;

  const body = await res.json();

  if (!res.ok) {
    const error = new Error(body.message || "API Error") as Error & {
      status: number;
      errors?: Record<string, string[]>;
    };
    error.status = res.status;
    error.errors = body.errors;
    throw error;
  }

  return body;
}

// --- Auth ---

export interface User {
  id: number;
  name: string;
}

interface AuthResponse {
  user: User;
  token: string;
}

export const auth = {
  register(data: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) {
    return request<AuthResponse>("/register", {
      method: "POST",
      body: JSON.stringify(data),
    });
  },

  login(data: { email: string; password: string }) {
    return request<AuthResponse>("/login", {
      method: "POST",
      body: JSON.stringify(data),
    });
  },

  logout() {
    return request<{ message: string }>("/logout", { method: "POST" });
  },
};

// --- Certifications ---

export interface Category {
  id: number;
  certification_id: number;
  name: string;
}

export interface Certification {
  id: number;
  name: string;
  exam_date: string | null;
  status: "studying" | "passed" | "failed";
  memo: string | null;
  categories: Category[];
}

export const certifications = {
  list(status?: string) {
    const params = status ? `?status=${status}` : "";
    return request<{ data: Certification[] }>(`/certifications${params}`);
  },

  get(id: number) {
    return request<{ data: Certification }>(`/certifications/${id}`);
  },

  create(data: {
    name: string;
    exam_date?: string;
    status?: string;
    memo?: string;
  }) {
    return request<{ data: Certification }>("/certifications", {
      method: "POST",
      body: JSON.stringify(data),
    });
  },

  update(
    id: number,
    data: { name?: string; exam_date?: string; status?: string; memo?: string }
  ) {
    return request<{ data: Certification }>(`/certifications/${id}`, {
      method: "PUT",
      body: JSON.stringify(data),
    });
  },

  delete(id: number) {
    return request<null>(`/certifications/${id}`, { method: "DELETE" });
  },
};

// --- Categories ---

export const categories = {
  list(certificationId: number) {
    return request<{ data: Category[] }>(
      `/certifications/${certificationId}/categories`
    );
  },

  create(certificationId: number, data: { name: string }) {
    return request<{ data: Category }>(
      `/certifications/${certificationId}/categories`,
      { method: "POST", body: JSON.stringify(data) }
    );
  },

  update(id: number, data: { name: string }) {
    return request<{ data: Category }>(`/categories/${id}`, {
      method: "PUT",
      body: JSON.stringify(data),
    });
  },

  delete(id: number) {
    return request<null>(`/categories/${id}`, { method: "DELETE" });
  },
};

// --- Study Logs ---

export interface StudyLog {
  id: number;
  certification_id: number;
  certification?: Certification;
  category_id: number | null;
  category?: Category | null;
  studied_on: string;
  duration_minutes: number;
  content: string | null;
}

interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    total: number;
  };
}

export const studyLogs = {
  list(params?: {
    certification_id?: number;
    category_id?: number;
    from?: string;
    to?: string;
    page?: number;
  }) {
    const searchParams = new URLSearchParams();
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined) searchParams.set(key, String(value));
      });
    }
    const query = searchParams.toString();
    return request<PaginatedResponse<StudyLog>>(
      `/study-logs${query ? `?${query}` : ""}`
    );
  },

  create(data: {
    certification_id: number;
    category_id?: number;
    studied_on: string;
    duration_minutes: number;
    content?: string;
  }) {
    return request<{ data: StudyLog }>("/study-logs", {
      method: "POST",
      body: JSON.stringify(data),
    });
  },

  update(
    id: number,
    data: {
      certification_id?: number;
      category_id?: number;
      studied_on?: string;
      duration_minutes?: number;
      content?: string;
    }
  ) {
    return request<{ data: StudyLog }>(`/study-logs/${id}`, {
      method: "PUT",
      body: JSON.stringify(data),
    });
  },

  delete(id: number) {
    return request<null>(`/study-logs/${id}`, { method: "DELETE" });
  },
};

// --- Dashboard ---

export interface DashboardCertification {
  id: number;
  name: string;
  exam_date: string | null;
  status: "studying" | "passed" | "failed";
  memo: string | null;
  total_minutes: number;
  total_hours: number;
  categories: { id: number; name: string }[];
}

export interface DashboardResponse {
  data: DashboardCertification[];
  summary: {
    total_certifications: number;
    total_minutes: number;
    total_hours: number;
  };
}

export const dashboard = {
  get() {
    return request<DashboardResponse>("/dashboard");
  },
};

// --- Calendar ---

export interface CalendarDay {
  date: string;
  total_minutes: number;
  count: number;
}

export const calendar = {
  list(year: number, month: number) {
    return request<{ year: number; month: number; days: CalendarDay[] }>(
      `/calendar?year=${year}&month=${month}`
    );
  },

  getByDate(date: string) {
    return request<{ data: StudyLog[] }>(`/calendar/${date}`);
  },
};
